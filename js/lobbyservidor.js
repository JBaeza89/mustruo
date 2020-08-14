const express = require('express');
const app = express();

//settings
app.set('port', 3300);

//start server
const server = app.listen(app.get('port'), () => {
    console.log("server on port", app.get('port'));
})

//websockets
const socketIo = require('socket.io');
const io = socketIo(server);
usuarios = [];
io.on('connection', (socket) => {
    console.log(`usuario ${socket.id} conectado`);
    socket.on('lobby', (data) => {        
        if (data != "anonimo") {           
            usuarios.push(data);
            usuarios = usuarios.sort((a, b) => {
                if (a.login.toLowerCase() < b.login.toLowerCase()) {
                    return -1;
                }
                return 1;
            });
        }
        io.emit('refrescarusuarios', usuarios);
    });

    socket.on('disconnect', () => {
        console.log("desconexion");
        usuarios = usuarios.filter((user) => {
            return user.socketid != socket.id;
        });
        io.emit('refrescarusuarios', usuarios);
    });

    socket.on('chatlobby:message', (data) => {
        io.emit('chatlobby:message', data);
    })

    socket.on('actualizarmesas', () => {
        io.emit('actualizarmesas');
    })

    socket.on('empezarpartida', (data) => {
        io.emit('empezarpartida', data);
    })

})