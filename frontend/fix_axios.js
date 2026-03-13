const fs = require('fs');

const files = [
    'src/pages/admin/CatalogManager.jsx',
    'src/pages/admin/PharmacyCatalog.jsx'
];

files.forEach(file => {
    let content = fs.readFileSync(file, 'utf8');
    content = content.replace(/import axios from 'axios';/, "import api from '../../api/axios';");
    content = content.replace(/axios\./g, 'api.');
    fs.writeFileSync(file, content);
});
