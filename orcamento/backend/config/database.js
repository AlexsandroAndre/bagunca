var mongoose = require('mongoose')

var dbUri = 'mongodb://localhost/db_orcamento'

module.exports = mongoose.connect(dbUri)

mongoose.connection.on('connected', function(){
	console.log('Mongoose default connection open to ' + dbUri)
})

mongoose.connection.on('error', function(){
	console.log('Mongoose default connection disconnected')
})

mongoose.connection.on('disconnected', function () {  
  console.log('Mongoose default connection disconnected')
})

mongoose.connection.on('open', function(){
	console.log('Mongoose default connection is open')
})

//errors messages
mongoose.Error.messages.general.required = "O atributo '{PATH}' é obrigatório."

process.on('SIGINT', function(){
	mongoose.connection.close(function(){
		console.log('Mongoose default connection disconnected through app termination')
		process.exit(0)
	})
})

//require('./create');