const _          = require('lodash')
const mongoose   = require('mongoose')
const User       = require('../../user/model/user')
const bcrypt     = require('bcryptjs')
const jwt        = require('jsonwebtoken')
const authConfig = require('../../../config/auth')

module.exports = {
    register : async (req, res) =>{
        const { email } = req.body
		try {
            if(await User.findOne({ email })){
                return res.status(400).send({ error: 'User already exists '})
            }
            const user = await User.create(req.body)
            user.password = undefined
            return res.send({ 
                user, 
                token: generateToken({ id: user.id })
            })
        } catch (error) {
            return res.status(400).send({ error: 'Registration failed' })
        }
    },
    authenticate: async (req, res) =>{
        const { email, password } = req.body
        try {
            const user = await User.findOne({ email }).select('+password')
            if(!user){
                return res.status(400).send({ error: 'User not found '})
            }

            if(!await bcrypt.compare(password, user.password)){
                return res.status(400).send({ error: 'Invalid password!' })
            }
            user.password = undefined

            return res.send({ 
                user, 
                token: generateToken({ id: user.id })
            })
        } catch (error) {
            return res.status(400).send({ error: 'Login failed' })
        }
    }
}

function generateToken(params = {}){
    return jwt.sign(params, authConfig.secret, {
        expiresIn: 86400
    })
}