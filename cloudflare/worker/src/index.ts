import { Hono, Context, Next } from 'hono'
import { cors } from 'hono/cors'
import { jwt, sign, verify } from 'hono/jwt'

type Bindings = {
  DB: D1Database
  JWT_SECRET: string
  RAZORPAY_KEY_ID: string
  RAZORPAY_KEY_SECRET: string
}

const app = new Hono<{ Bindings: Bindings }>()

app.use('*', cors())

app.get('/', (c) => c.text('Wheel Collectors API is running!'))

// Auth Middleware (Basic JWT)
const auth = (c: Context, next: Next) => jwt({ secret: c.env.JWT_SECRET, alg: 'HS256' })(c, next)

// Admin Auth Middleware (JWT + Role Check)
// Admin Auth Middleware (JWT + Role Check)
const adminAuth = async (c: Context, next: Next) => {
  const authHeader = c.req.header('Authorization');
  if (!authHeader) {
    return c.json({ error: 'Unauthorized: No token provided' }, 401);
  }
  
  const token = authHeader.replace('Bearer ', '');
  try {
    const payload = await verify(token, c.env.JWT_SECRET, 'HS256');
    if (payload && payload.role === 'admin') {
      c.set('jwtPayload', payload);
      return await next();
    }
    return c.json({ error: 'Forbidden: Admin access required' }, 403);
  } catch (err) {
    return c.json({ error: 'Unauthorized: Invalid or expired token. Please login again.' }, 401);
  }
};

app.post('/api/auth/register', async (c) => {
  const { name, email, password } = await c.req.json()
  
  if (!name || !email || !password) return c.json({ error: 'All fields required' }, 400)
  
  const existing = await c.env.DB.prepare('SELECT id FROM users WHERE email = ?').bind(email).first()
  if (existing) return c.json({ error: 'Email already registered' }, 409)

  // In production, bcrypt hash the password here using subtle.crypto
  const { success } = await c.env.DB.prepare(
    'INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)'
  ).bind(name, email, password, 'user').run()

  if (success) return c.json({ success: true }, 201)
  return c.json({ error: 'Failed to create account' }, 500)
})

// Auth API
app.post('/api/auth/login', async (c) => {
  const { email, password } = await c.req.json()
  
  const user: any = await c.env.DB.prepare('SELECT * FROM users WHERE email = ?').bind(email).first()
  
  // Placeholder: In production use subtle.crypto and bcrypt
  if (user && password === 'admin123') { 
    const token = await sign({ id: user.id, email: user.email, role: user.role, exp: Math.floor(Date.now() / 1000) + 60 * 60 * 24 }, c.env.JWT_SECRET)
    return c.json({ token, user: { id: user.id, name: user.name, role: user.role } })
  }
  
  return c.json({ error: 'Invalid credentials' }, 401)
})

// Order API
app.post('/api/orders', async (c) => {
  const { items, customer_name, customer_email, customer_phone, city, pincode, state, address, userId } = await c.req.json()
  
  if (!items || items.length === 0) return c.json({ error: 'Empty cart' }, 400)
  
  let subtotal = 0
  for (const item of items) {
    const p: any = await c.env.DB.prepare('SELECT price FROM products WHERE id = ?').bind(item.productId).first()
    if (p) subtotal += Number(p.price) * item.quantity
  }

  // Shipping Logic
  let shippingCost = 0
  if (subtotal < 500) {
    const kerala = ['Kerala']
    const middleIndia = ['Tamil Nadu', 'Karnataka', 'Andhra Pradesh', 'Telangana', 'Maharashtra', 'Goa', 'Puducherry', 'Lakshadweep', 'Odisha', 'Gujarat', 'Chhattisgarh']
    
    if (kerala.includes(state)) {
      shippingCost = 50
    } else if (middleIndia.includes(state)) {
      shippingCost = 70
    } else {
      // North / East / Others
      shippingCost = 80
    }
  }

  const totalPrice = subtotal + shippingCost

  const { meta } = await c.env.DB.prepare(
    'INSERT INTO orders (user_id, total_price, customer_name, customer_email, customer_phone, city, pincode, state, shipping_cost, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
  ).bind(userId || null, totalPrice, customer_name, customer_email, customer_phone, city, pincode, state, shippingCost, address).run()
  
  const orderId = meta.last_row_id

  for (const item of items) {
    await c.env.DB.prepare(
      'INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)'
    ).bind(orderId, item.productId, item.quantity, item.price).run()
  }

  return c.json({ success: true, orderId })
})

// Categories API
// --- ADMIN ENDPOINTS (Protected with adminAuth) ---

// Admin Orders
app.get('/api/admin/orders', adminAuth, async (c) => {
  try {
    const { results: orders } = await c.env.DB.prepare('SELECT * FROM orders ORDER BY created_at DESC').all()
    
    // Fetch items for each order
    const ordersWithItems = await Promise.all(orders.map(async (order: any) => {
      const { results: items } = await c.env.DB.prepare(`
        SELECT oi.*, p.name as product_name, p.image_url 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
      `).bind(order.id).all()
      return { ...order, items }
    }))

    return c.json(ordersWithItems)
  } catch (err: any) {
    return c.json({ error: err.message }, 500)
  }
})

// Products CRUD
app.get('/api/admin/products', adminAuth, async (c) => {
  const { results } = await c.env.DB.prepare('SELECT * FROM products ORDER BY id DESC').all()
  return c.json(results)
})

app.post('/api/admin/products', adminAuth, async (c) => {
  try {
    const p = await c.req.json()
    const { meta } = await c.env.DB.prepare(
      'INSERT INTO products (category_id, name, subtitle, price, rating, review_count, image_url, badge, description, is_featured, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    ).bind(p.category_id, p.name, p.subtitle, p.price, p.rating || 0, p.review_count || 0, p.image_url, p.badge, p.description, p.is_featured || 0, p.stock || 0).run()
    
    const productId = meta.last_row_id;

    // Handle multiple images
    if (p.images && Array.isArray(p.images)) {
      for (const [index, imgPath] of p.images.entries()) {
        await c.env.DB.prepare(
          'INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)'
        ).bind(productId, imgPath, index === 0 ? 1 : 0).run()
      }
    }

    return c.json({ success: true, id: productId })
  } catch (err: any) {
    return c.json({ error: err.message }, 500)
  }
})

app.patch('/api/admin/products/:id', adminAuth, async (c) => {
  try {
    const id = c.req.param('id')
    const p = await c.req.json()
    
    await c.env.DB.prepare(
      'UPDATE products SET category_id=?, name=?, subtitle=?, price=?, image_url=?, badge=?, description=?, is_featured=?, stock=? WHERE id=?'
    ).bind(p.category_id, p.name, p.subtitle, p.price, p.image_url, p.badge, p.description, p.is_featured, p.stock, id).run()

    // Sync images if provided
    if (p.images && Array.isArray(p.images)) {
      // Clear existing
      await c.env.DB.prepare('DELETE FROM product_images WHERE product_id = ?').bind(id).run()
      
      // Add new
      for (const [index, imgPath] of p.images.entries()) {
        await c.env.DB.prepare(
          'INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)'
        ).bind(id, imgPath, index === 0 ? 1 : 0).run()
      }
    }

    return c.json({ success: true })
  } catch (err: any) {
    return c.json({ error: err.message }, 500)
  }
})

app.delete('/api/admin/products/:id', adminAuth, async (c) => {
  const id = c.req.param('id')
  await c.env.DB.prepare('DELETE FROM products WHERE id = ?').bind(id).run()
  return c.json({ success: true })
})

// Categories CRUD
app.post('/api/admin/categories', adminAuth, async (c) => {
  const { name, slug, image_url } = await c.req.json()
  const { meta } = await c.env.DB.prepare(
    'INSERT INTO categories (name, slug, image_url) VALUES (?, ?, ?)'
  ).bind(name, slug, image_url).run()
  return c.json({ success: true, id: meta.last_row_id })
})

app.patch('/api/admin/categories/:id', adminAuth, async (c) => {
  const id = c.req.param('id')
  const { name, slug, image_url } = await c.req.json()
  await c.env.DB.prepare(
    'UPDATE categories SET name = ?, slug = ?, image_url = ? WHERE id = ?'
  ).bind(name, slug, image_url, id).run()
  return c.json({ success: true })
})

app.delete('/api/admin/categories/:id', adminAuth, async (c) => {
  const id = c.req.param('id')
  // Check if products exist in this category first
  const count: any = await c.env.DB.prepare('SELECT COUNT(*) as count FROM products WHERE category_id = ?').bind(id).first('count')
  if (count && Number(count) > 0) {
    return c.json({ error: 'Cannot delete category with associated products' }, 400)
  }
  await c.env.DB.prepare('DELETE FROM categories WHERE id = ?').bind(id).run()
  return c.json({ success: true })
})

// User Management (Admin)
app.get('/api/admin/users', adminAuth, async (c) => {
  const { results } = await c.env.DB.prepare('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC').all()
  return c.json(results)
})

app.patch('/api/admin/users/:id', adminAuth, async (c) => {
  const id = c.req.param('id')
  const { role } = await c.req.json()
  
  // Prevent removing own admin privileges
  const payload = c.get('jwtPayload') as any
  if (payload.id == id && role !== 'admin') {
    return c.json({ error: 'You cannot remove your own admin privileges' }, 400)
  }

  await c.env.DB.prepare('UPDATE users SET role = ? WHERE id = ?').bind(role, id).run()
  return c.json({ success: true })
})

app.delete('/api/admin/users/:id', adminAuth, async (c) => {
  const id = c.req.param('id')
  const payload = c.get('jwtPayload') as any
  
  if (payload.id == id) {
    return c.json({ error: 'You cannot delete your own account' }, 400)
  }

  await c.env.DB.prepare('DELETE FROM users WHERE id = ?').bind(id).run()
  return c.json({ success: true })
})

app.get('/api/categories', async (c) => {
  const { results } = await c.env.DB.prepare('SELECT * FROM categories ORDER BY name ASC').all()
  return c.json(results)
})

app.get('/api/categories/:id', async (c) => {
  const id = c.req.param('id')
  const cat = await c.env.DB.prepare('SELECT * FROM categories WHERE id = ?').bind(id).first()
  return c.json(cat)
})

// Products API
app.get('/api/products', async (c) => {
  const categoryId = c.req.query('category')
  const search = c.req.query('search')
  const featured = c.req.query('featured')
  const ids = c.req.query('ids')
  
  let query = 'SELECT * FROM products WHERE 1=1'
  const params = []

  if (ids) {
    const idList = ids.split(',').map(id => parseInt(id)).filter(id => !isNaN(id))
    if (idList.length > 0) {
      query += ` AND id IN (${idList.map(() => '?').join(',')})`
      params.push(...idList)
    }
  }

  if (categoryId) {
    query += ' AND category_id = ?'
    params.push(categoryId)
  }
  
  if (search) {
    query += ' AND (name LIKE ? OR subtitle LIKE ? OR description LIKE ?)'
    const searchTerm = `%${search}%`
    params.push(searchTerm, searchTerm, searchTerm)
  }

  if (featured === '1') {
    query += ' AND is_featured = 1'
  }

  const { results } = await c.env.DB.prepare(query).bind(...params).all()
  return c.json(results)
})

// Product Details
app.get('/api/products/:id', async (c) => {
  const id = c.req.param('id')
  const product = await c.env.DB.prepare('SELECT * FROM products WHERE id = ?').bind(id).first()
  if (!product) return c.json({ error: 'Not found' }, 404)
  
  const { results: images } = await c.env.DB.prepare('SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC').bind(id).all()
  
  return c.json({ ...product, images })
})

// Razorpay: Create Order
app.post('/api/payments/create-order', async (c) => {
  const { amount, orderId } = await c.req.json()
  
  if (!amount || !orderId) return c.json({ error: 'Missing parameters' }, 400)

  const auth = btoa(`${c.env.RAZORPAY_KEY_ID}:${c.env.RAZORPAY_KEY_SECRET}`)
  
  try {
    const res = await fetch('https://api.razorpay.com/v1/orders', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Basic ${auth}`
      },
      body: JSON.stringify({
        amount: amount * 100, // INR to Paise
        currency: 'INR',
        receipt: `order_rcpt_${orderId}`
      })
    })

    const data: any = await res.json()
    if (data.error) throw new Error(data.error.description)
    
    return c.json(data)
  } catch (err: any) {
    return c.json({ error: err.message }, 500)
  }
})

// Razorpay: Verify Payment
app.post('/api/payments/verify', async (c) => {
  const { razorpay_order_id, razorpay_payment_id, razorpay_signature, internal_order_id } = await c.req.json()

  const secret = c.env.RAZORPAY_KEY_SECRET
  const payload = razorpay_order_id + '|' + razorpay_payment_id
  
  // HMAC SHA256 Verification using Web Crypto
  const encoder = new TextEncoder()
  const key = await crypto.subtle.importKey(
    'raw', 
    encoder.encode(secret), 
    { name: 'HMAC', hash: 'SHA-256' }, 
    false, 
    ['sign']
  )
  const signature = await crypto.subtle.sign('HMAC', key, encoder.encode(payload))
  const expectedSignature = Array.from(new Uint8Array(signature))
    .map(b => b.toString(16).padStart(2, '0'))
    .join('')

  if (expectedSignature === razorpay_signature) {
    // Update Order Status in D1
    await c.env.DB.prepare(
      'UPDATE orders SET status = ?, address = address || ? WHERE id = ?'
    ).bind('Paid', ` | Payment ID: ${razorpay_payment_id}`, internal_order_id).run()
    
    return c.json({ success: true })
  }

  return c.json({ error: 'Invalid signature' }, 400)
})

export default app
