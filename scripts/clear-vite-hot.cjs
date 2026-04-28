/* Remove Laravel Vite "hot" file after production build so @vite uses manifest, not a dead dev server. */
const fs = require('fs');
const path = require('path');

const hot = path.join(__dirname, '..', 'public', 'hot');
try {
    fs.unlinkSync(hot);
} catch (err) {
    if (err && err.code !== 'ENOENT') {
        throw err;
    }
}
