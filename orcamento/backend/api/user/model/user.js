const mongoose = require('mongoose')
const bcrypt   = require('bcryptjs')
const SALT_WORK_FACTOR = 10

const UserSchema = new mongoose.Schema({
    name: {
        type: String,
        require: true
    },
    email: {
        type: String,
        unique: true,
        required: true,
        lowercase: true
    },
    password: {
        type: String,
        required: true,
        select: false
    },
    createdAt: {
        type: Date,
        default: Date.now
    }
})

UserSchema.pre('save', function(next){
    var user = this;
	//verifica se foi alterado o hash
	if(!user.isModified('password')) return next();
	//generate a salt
	bcrypt.genSalt(SALT_WORK_FACTOR, function(err, salt){
		if(err) return next(err);
		// hash the password using our new salt
		bcrypt.hash(user.password, salt, function(err, hash) {
			if(err) return next(err);
			//override the cleartext password with the hashed one
			user.password = hash;
			next()
		})
	})
})

const User = mongoose.model('User', UserSchema)
module.exports = User