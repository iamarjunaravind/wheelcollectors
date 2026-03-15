import { Hono, Context, Next } from 'hono'
import { cors } from 'hono/cors'
import { jwt, sign } from 'hono/jwt'

type Bindings = {
  DB: D1Database
  JWT_SECRET: string
  RAZORPAY_KEY_ID: string
  RAZORPAY_KEY_SECRET: string
}

const app = new Hono<{ Bindings: Bindings }>()

app.use('*', cors())

app.get('/', (c) => c.text('Wheel Collectors API is running!'))

// Auth Middleware (Protected routes)
const auth = (c: Context, next: Next) => jwt({ secret: c.env.JWT_SECRET, alg: 'HS256' })(c, next)

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
  
  const user = await c.env.DB.prepare('SELECT * FROM users WHERE email = ?').bind(email).first()
  
  // Placeholder: In production use subtle.crypto and bcrypt
  if (user && password === 'admin123') { 
    const token = await sign({ id: user.id, email: user.email, role: user.role, exp: Math.floor(Date.now() / 1000) + 60 * 60 * 24 }, c.env.JWT_SECRET)
    return c.json({ token, user: { id: user.id, name: user.name, role: user.role } })
  }
  
  return c.json({ error: 'Invalid credentials' }, 401)
})

// Order API
app.post('/api/orders', async (c) => {
  const { items, address, userId } = await c.req.json()
  
  if (!items || items.length === 0) return c.json({ error: 'Empty cart' }, 400)
  
  let totalPrice = 0
  for (const item of items) {
    const p = await c.env.DB.prepare('SELECT price FROM products WHERE id = ?').bind(item.productId).first()
    if (p) totalPrice += p.price * item.quantity
  }

  const { meta } = await c.env.DB.prepare(
    'INSERT INTO orders (user_id, total_price, address) VALUES (?, ?, ?)'
  ).bind(userId || null, totalPrice, address).run()
  
  const orderId = meta.last_row_id

  for (const item of items) {
    await c.env.DB.prepare(
      'INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)'
    ).bind(orderId, item.productId, item.quantity, item.price).run()
  }

  return c.json({ success: true, orderId })
})

// Categories API
// --- ADMIN ENDPOINTS (Protected) ---

// Products CRUD
app.get('/api/admin/products', auth, async (c) => {
  const { results } = await c.env.DB.prepare('SELECT * FROM products ORDER BY id DESC').all()
  return c.json(results)
})

app.post('/api/admin/products', auth, async (c) => {
  const p = await c.req.json()
  const { meta } = await c.env.DB.prepare(
    'INSERT INTO products (category_id, name, subtitle, price, rating, review_count, image_url, badge, description, is_featured, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
  ).bind(p.category_id, p.name, p.subtitle, p.price, p.rating || 0, p.review_count || 0, p.image_url, p.badge, p.description, p.is_featured || 0, p.stock || 0).run()
  return c.json({ success: true, id: meta.last_row_id })
})

app.patch('/api/admin/products/:id', auth, async (c) => {
  const id = c.req.param('id')
  const p = await c.req.json()
  await c.env.DB.prepare(
    'UPDATE products SET category_id=?, name=?, subtitle=?, price=?, image_url=?, badge=?, description=?, is_featured=?, stock=? WHERE id=?'
  ).bind(p.category_id, p.name, p.subtitle, p.price, p.image_url, p.badge, p.description, p.is_featured, p.stock, id).run()
  return c.json({ success: true })
})

app.delete('/api/admin/products/:id', auth, async (c) => {
  const id = c.req.param('id')
  await c.env.DB.prepare('DELETE FROM products WHERE id = ?').bind(id).run()
  return c.json({ success: true })
})

// Categories CRUD
app.post('/api/admin/categories', auth, async (c) => {
  const { name, slug, image_url } = await c.req.json()
  const { meta } = await c.env.DB.prepare(
    'INSERT INTO categories (name, slug, image_url) VALUES (?, ?, ?)'
  ).bind(name, slug, image_url).run()
  return c.json({ success: true, id: meta.last_row_id })
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
