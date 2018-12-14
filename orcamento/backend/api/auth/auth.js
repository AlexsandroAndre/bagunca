var express        = require('express')
var router         = express.Router()
var authController = require('../auth/controller/authController')

router.post('/register', authController.register)
router.post('/authenticate', authController.authenticate)

module.exports = router