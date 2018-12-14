const _ = require('lodash');
const mongoose = require('mongoose')
const User     = require('../model/user')


module.exports = {
	list : function(req, res){
		try {
            User.find((error, user)=>{
                if(!user){
                    return res.status(500).json({errors:[error]})
                }
                return res.send({user})
            })
        } catch (error) {
            return res.status(400).send(error)
        }
    },  
	show : function(req, res){
		const id = req.params.id 
        try {
            User.findById({ _id : id }, (error, user)=>{
                if(!user) {
                    return res.status(500).json({errors:[error]})
                }
                return res.json(user)
            })
        } catch (error) {
            return res.status(400).send(error)
        }
	},
	create : async (req, res) =>{
        const { email } = req.body
		try {
            if(await User.findOne({ email })){
                return res.status(400).send({ error: 'User already exists '})
            }
            const user = await User.create(req.body)
            user.password = undefined
            return res.send({ user })
        } catch (error) {
            return res.status(400).send({ error: 'Registration failed' })
        }
    },
	update : function(req, res){
		const id = req.params.id
        try {
            User.findOne({_id : id}, (error, result)=>{
                if(error){
                    return res.status(500).json({errors:[error]})
                }
                result.name     = req.body.name ? req.body.name : result.name
                result.email    = req.body.email ? req.body.email : result.email
                result.password = req.body.password ? req.body.password : result.password                
                result.save((err, data)=>{                    
                    if(err){
                        return res.status(500).json({errors:[err]})
                    }
                    return res.json({data})
                })
            })
        } catch (error) {
            return res.status(400).send({ error: 'Update failed' })
        }
    },
	remove : function(req, res){
		const id = req.params.id
        try {
            User.findByIdAndRemove({ _id: id }, (error, result)=>{
                if(error){
                    return res.status(500).json({errors:[error]})
                }
                return res.json({result})
            })
        } catch (error) {
            return res.status(400).send({ error: 'Removed failed' })
        }
    }
}

