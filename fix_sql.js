const fs = require('fs');
const path = 'cloudflare/worker/full_migration.sql';

let content = fs.readFileSync(path, 'utf8');

// Replace unistr('...') with '...'
// This handles single quotes inside the string by using a non-greedy match and checking for the wrapper
content = content.replace(/unistr\('((?:[^']|'')*)'\)/g, (match, p1) => {
    // Replace \uXXXX with characters
    let decoded = p1.replace(/\\u([0-9a-fA-F]{4})/g, (u, code) => {
        return String.fromCharCode(parseInt(code, 16));
    });
    return `'${decoded}'`;
});

fs.writeFileSync(path, content, 'utf8');
console.log('Successfully cleaned unistr calls and decoded Unicode sequences.');
