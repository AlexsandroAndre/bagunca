const express = require('express')

module.exports = function(server){
    //api routes
    const router = express.Router()

    server.use(function(req, res, next) {
          res.setHeader('Access-Control-Allow-Credentials', true)
          res.setHeader('Access-Control-Allow-Origin', '*')
          res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, PATCH, DELETE')
          res.setHeader('Access-Control-Allow-Headers', 'X-Requested-With,content-type')
          next()
     })

    server.use('/api', router)

    //controllers
    const user = require('../api/user/user')
    const auth = require('../api/auth/auth')
    const middlewares = require('../api/middlewares/auth')
    
    //routas da API
    router.use('/auth', auth)
    router.use(middlewares)
    router.use('/user', user)        
}