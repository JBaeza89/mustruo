const express = require('express');
const app = express();
const fetch = require('node-fetch');

//settings
app.set('port', 3333);

//start server
const server = app.listen(app.get('port'), () => {
    console.log("server on port", app.get('port'));
})

//websockets
const socketIo = require('socket.io');
const io = socketIo(server);
var jugadores = [];
io.on('connection', (socket) => {
    console.log(`usuario ${socket.id} conectado`);
    socket.on('partida', (data) => {
        jugadores.push(data);
        let datos = new Object();
        datos.users = jugadores.filter((us) => {
            return us.id_mesa == data.id_mesa;
        });
        datos.users = datos.users.sort((a, b) => {
            if (a.login.toLowerCase() < b.login.toLowerCase()) {
                return -1;
            }
            return 1;
        });
        datos.mesa = data.id_mesa;
        io.emit('refrescarusuarios', datos)
    })

    socket.on('disconnect', () => {
        userDesconectado = jugadores.filter((user) => {
            return user.socket_id == socket.id;
        })[0];
        jugadores = jugadores.filter((user) => {
            return user != userDesconectado;
        });
        let datos = new Object();
        datos.mesa = userDesconectado.id_mesa;
        datos.users = jugadores.filter((us) => {
            return us.id_mesa == datos.mesa;
        });
        io.emit('refrescarusuarios', datos);
    });

    socket.on('chatpartida:message', (data) => {
        io.emit('chatpartida:message', data);
    })

    socket.on('interaccion', (data) => {
        io.emit('interaccion', data);
    })

    socket.on('actualizarMarcadores', (data) => {
        io.emit('actualizarMarcadores', data);
    })

    socket.on('repartir', (data) => {
        fetch(`http://Localhost/Mustruo/api/repartir/${data}`)
            .then((res) => res.json())
            .then((res) => {
                io.emit('repartir', res);
            });
    })

    socket.on('mostrarbocadillo', (data) => {
        io.emit('mostrarbocadillo', data);
    })

    socket.on('showdown', (data) => {
        var contador = 0;
        var id = data.mesa_id;
        var datos = new Object();
        datos.textos = [];
        datos.marcadores = [];
        fetch(`http://Localhost/Mustruo/api/mostrarcartas/${id}/control`)
            .then((res) => res.json())
            .then((res) => {
                io.emit('showdown:levantarcartas', res);
                if (data.estado == 'ordago') {
                    setTimeout(() => {
                        fetch(`http://Localhost/Mustruo/api/resolverordago/${id}/control`)
                            .then((res) => res.json())
                            .then((res) => {
                                res.mesa_id = id;
                                io.emit('showdown:ordago', res);
                                setTimeout(() => {
                                    io.emit('actualizarMarcadores', res);
                                    fetch(`http://Localhost/Mustruo/api/adelantarmano/${id}/control`)
                                        .then((res) => res.json())
                                        .then((res) => {
                                            io.emit('tapete', res.mesa_id);
                                            io.emit('interaccion', res);
                                        });
                                }, 2000);
                            });
                    }, 2000);
                } else {
                    fetch(`http://Localhost/Mustruo/api/resolvergrande/${id}/control`)
                        .then((res) => res.json())
                        .then((res) => {
                            if (res.texto != "nada") {
                                datos.textos[contador] = res.texto;
                                datos.marcadores[contador] = res.marcador;
                                contador++;
                            }
                            fetch(`http://Localhost/Mustruo/api/resolverchica/${id}/control`)
                                .then((res) => res.json())
                                .then((res) => {
                                    if (res.texto != "nada") {
                                        datos.textos[contador] = res.texto;
                                        datos.marcadores[contador] = res.marcador;
                                        contador++;
                                    }
                                    fetch(`http://Localhost/Mustruo/api/resolverpares/${id}/control`)
                                        .then((res) => res.json())
                                        .then((res) => {
                                            if (res.texto != "nada") {
                                                datos.textos[contador] = res.texto;
                                                datos.marcadores[contador] = res.marcador;
                                                contador++;
                                            }
                                            fetch(`http://Localhost/Mustruo/api/resolverjuego/${id}/control`)
                                                .then((res) => res.json())
                                                .then((res) => {
                                                    if (res.texto != "nada") {
                                                        datos.textos[contador] = res.texto;
                                                        datos.marcadores[contador] = res.marcador;
                                                        contador++;
                                                    }
                                                    fetch(`http://Localhost/Mustruo/api/resolverpunto/${id}/control`)
                                                        .then((res) => res.json())
                                                        .then((res) => {
                                                            if (res.texto != "nada") {
                                                                datos.textos[contador] = res.texto;
                                                                datos.marcadores[contador] = res.marcador;                                                                
                                                                contador++;
                                                            }
                                                            datos.contador = contador;
                                                            datos.mesa = id;
                                                            io.emit('showdown', datos);
                                                            setTimeout(() => {
                                                                fetch(`http://Localhost/Mustruo/api/adelantarmano/${id}/control`)
                                                                    .then((res) => res.json())
                                                                    .then((res) => {
                                                                        io.emit('tapete', res.mesa_id);
                                                                        io.emit('interaccion', res);
                                                                    });
                                                            }, 4000 * contador);
                                                        });
                                                });
                                        });
                                });
                        });
                }
            });
    })

    socket.on('salirdemesa', (data) => {
        io.emit('salirdemesa', (data));
    })

})