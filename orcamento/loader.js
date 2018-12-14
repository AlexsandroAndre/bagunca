const server = require('./backend/config/server.js')
require('./backend/config/database')
require('./backend/config/routes.js')(server)