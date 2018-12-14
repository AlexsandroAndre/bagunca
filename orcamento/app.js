'user strict'

const express      = require('express')
const bodyParser   = require('body-parser')
const app          = express()

app.use(bodyParser.json())
app.use(bodyParser.urlencoded({ extended: false }))

app.use(function(err, req, res, next) {
    // set locals, only providing error in development
    res.locals.message = err.message
    res.locals.error = req.app.get('env') === 'development' ? err : {}
  
    // render the error page
    res.status(err.status || 500);
    res.render('error')
  })
  
  module.exports = app