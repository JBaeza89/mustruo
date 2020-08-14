//variables
const socket = io('http://localhost:3333');
var chat = document.getElementById("chat");
var usuarios = document.getElementById("usuariosonline");
var enviar = document.getElementById("enviarmensaje");
var mensaje = document.getElementById("mensaje");
var timeoutBocadillo = 2000;


//functions
function enviarTexto() {
    if (mensaje.value != "") {
        socket.emit("chatpartida:message", {
            "mensaje": mensaje.value,
            "nombre": usuario.login,
            "mesa": mesa.id_mesa
        })
        mensaje.value = "";
    }
}

function hacerLista(data) {
    var container = document.createElement("div");
    var previo = "";
    for (let i = 0; i < data.length; i++) {
        let login = data[i].login;
        if (login != previo) {
            let li = document.createElement("li");
            let div = document.createElement("div");
            div.setAttribute("class", "online");
            div.innerHTML = `<a>${login}</a>`;
            li.append(div);
            container.append(li);
            li.addEventListener("click", () => {
                renderStats(login);
            });
            previo = login;
        }
    }
    usuarios.innerHTML = "";
    usuarios.append(container);
}

function renderStats(login) {
    fetch(`${path}/api/estadisticas/${login}`)
        .then((res) => res.json())
        .then((res) => {
            $("#usuestadisticas").text(login);
            $("#juegosJugados").text(res.juegos_jugados);
            $("#juegosGanados").text(res.juegos_ganados);
            $("#vacasJugadas").text(res.vacas_jugadas);
            $("#vacasGanadas").text(res.vacas_ganadas);
            $("#abandonos").text(res.abandonos);
        });
}

function repartoDerecha(cartas) {
    cartas[0].setAttribute('class', 'ocultar aparecer unSegundo')
    cartas['1'].setAttribute('class', 'ocultar aparecer cincoSegundos')
    cartas['2'].setAttribute('class', 'ocultar aparecer nueveSegundos')
    cartas['3'].setAttribute('class', 'ocultar aparecer treceSegundos')
}
function repartoCompi(cartas) {
    cartas['0'].setAttribute('class', 'ocultar aparecer dosSegundos')
    cartas['1'].setAttribute('class', 'ocultar aparecer seisSegundos')
    cartas['2'].setAttribute('class', 'ocultar aparecer diezSegundos')
    cartas['3'].setAttribute('class', 'ocultar aparecer catorceSegundos')
}
function repartoIzq(cartas) {
    cartas['0'].setAttribute('class', 'ocultar aparecer tresSegundos')
    cartas['1'].setAttribute('class', 'ocultar aparecer sieteSegundos')
    cartas['2'].setAttribute('class', 'ocultar aparecer onceSegundos')
    cartas['3'].setAttribute('class', 'ocultar aparecer quinceSegundos')
}
function repartoUsuario(cartas) {
    cartas['0'].setAttribute('class', 'ocultar aparecer cuatroSegundos')
    cartas['1'].setAttribute('class', 'ocultar aparecer ochoSegundos')
    cartas['2'].setAttribute('class', 'ocultar aparecer doceSegundos')
    cartas['3'].setAttribute('class', 'ocultar aparecer dieciseisSegundos')
}
function hiddenReparto() {
    if (!$('#deUsu').hasClass('hidden')) {
        $('#deUsu').addClass('hidden');
    }
    if (!$('#deRivalDer').hasClass('hidden')) {
        $('#deRivalDer').addClass('hidden');
    }
    if (!$('#deCompi').hasClass('hidden')) {
        $('#deCompi').addClass('hidden');
    }
    if (!$('#deRivalIzq').hasClass('hidden')) {
        $('#deRivalIzq').addClass('hidden');
    }
}
function repartir() {
    const manoUsuario = $('.manoUsuario');
    const manoRivalDer = $('.manoRivalDer');
    const manoRivalIzq = $('.manoRivalIzq');
    const manoCompi = $('.manoCompi');
    var usuarioCartas = $('.misCartas').children();
    var compiCartas = $('.compiCartas').children();
    var rivalDerCartas = $('.rivalderCartas').children();
    var rivalIzqCartas = $('.rivalizqCartas').children();
    if (!manoRivalDer.hasClass('hidden')) {
        $('#deUsu').removeClass('hidden');
        repartoDerecha(rivalDerCartas);
        repartoCompi(compiCartas);
        repartoIzq(rivalIzqCartas);
        repartoUsuario(usuarioCartas);
    } else if (!manoCompi.hasClass('hidden')) {
        $('#deRivalDer').removeClass('hidden');
        repartoDerecha(compiCartas);
        repartoCompi(rivalIzqCartas);
        repartoIzq(usuarioCartas);
        repartoUsuario(rivalDerCartas);
    } else if (!manoUsuario.hasClass('hidden')) {
        $('#deRivalIzq').removeClass('hidden');
        repartoDerecha(usuarioCartas);
        repartoCompi(rivalDerCartas);
        repartoIzq(compiCartas);
        repartoUsuario(rivalIzqCartas);
    } else if (!manoRivalIzq.hasClass('hidden')) {
        $('#deCompi').removeClass('hidden');
        repartoDerecha(rivalIzqCartas);
        repartoCompi(usuarioCartas);
        repartoIzq(rivalDerCartas);
        repartoUsuario(compiCartas);
    }
}

function subirCartas() {
    if ($(this).hasClass('subirCarta')) {
        $(this).removeClass('subirCarta');
    } else {
        $(this).addClass('subirCarta');
    }
}

function activarSubidaCartas() {
    $('#carta1').attr('class', 'pointer');
    $('#carta1').click(subirCartas);
    $('#carta2').attr('class', 'pointer');
    $('#carta2').click(subirCartas);
    $('#carta3').attr('class', 'pointer');
    $('#carta3').click(subirCartas);
    $('#carta4').attr('class', 'pointer');
    $('#carta4').click(subirCartas);
}

function desactivarSubidaCartas() {
    $('#carta1').off('click');
    $('#carta1').attr('class', '');
    $('#carta2').off('click');
    $('#carta2').attr('class', '');
    $('#carta3').off('click');
    $('#carta3').attr('class', '');
    $('#carta4').off('click');
    $('#carta4').attr('class', '');
}

function actualizarDatosJugada(situacion) {
    $("#grande").text(situacion.grande);
    $("#chica").text(situacion.chica);
    $("#pares").text(situacion.pares);
    $("#juego").text(situacion.juego);
    $("#punto").text(situacion.punto);
}

function mostrarMenu(estado) {
    if (estado == "limpio") {
        $("#nohayEnvite").removeClass("hidden");
    } else if (estado == "envite") {
        $("#hayEnvite").removeClass("hidden");
    } else if (estado == "ordago") {
        $("#responderOrdago").removeClass("hidden");
    }
}

function ocultarCartas() {
    for (let i = 1; i <= 4; i++) {
        $(`#carta${i}`).addClass("hidden");
        $(`#compi${i}`).addClass("hidden");
        $(`#rivalizq${i}`).addClass("hidden");
        $(`#rivalder${i}`).addClass("hidden");
    }
}

function mostrarCartas() {
    for (let i = 1; i <= 4; i++) {
        $(`#carta${i}`).removeClass("hidden");
        $(`#compi${i}`).removeClass("hidden");
        $(`#rivalizq${i}`).removeClass("hidden");
        $(`#rivalder${i}`).removeClass("hidden");
    }
}

function colocarBaraja(mano) {
    switch (mano) {
        case usuario.posicion:
            $("#manousuario").removeClass("hidden");
            break;
        case compannero.posicion:
            $("#manocompi").removeClass("hidden");
            break;
        case rivalIzq.posicion:
            $("#manorivalizq").removeClass("hidden");
            break;
        case rivalDer.posicion:
            $("#manorivalder").removeClass("hidden");
            break;
    }
}

function ocultarBaraja() {
    if (!$("#manousuario").hasClass("hidden")) {
        $("#manousuario").addClass("hidden")
    } else if (!$("#manocompi").hasClass("hidden")) {
        $("#manocompi").addClass("hidden");
    } else if (!$("#manorivalizq").hasClass("hidden")) {
        $("#manorivalizq").addClass("hidden")
    } else {
        $("#manorivalder").addClass("hidden");
    }
}

function colocarReversos() {
    for (let i = 1; i <= 4; i++) {
        $(`#compi${i}`).attr('src', `${path}/media/baraja/reverso.jpg`);
        $(`#rivalizq${i}`).attr('src', `${path}/media/baraja/reverso.jpg`);
        $(`#rivalder${i}`).attr('src', `${path}/media/baraja/reverso.jpg`);
    }
}

function colocarMisCartas() {
    for (let i = 1; i <= 4; i++) {
        $(`#carta${i}`).attr('src', `${path}/media/baraja/${cartas[i - 1].imagen}`);
    }
}

function comprobarYMostrarMenu(mano, turno, jugada, estado) {
    if (estado != "repartir" && $("#carta1").hasClass("hidden")) {
        mostrarCartas();
    }
    if (estado == "repartir") {
        /*if (!$("#carta1").hasClass("hidden")) {
            ocultarCartas()
        }*/
        if ((mano + 3) % 4 == usuario.posicion) {
            $("#repartir").removeClass("hidden");
        }
    } else if (usuario.posicion == (mano + turno) % 4) {
        if (jugada == "mus") {
            if (estado == "menu") {
                $("#haymus").removeClass("hidden");
            } else if (estado == "descartando") {
                $("#divDescartes").removeClass("hidden");
                activarSubidaCartas();
            }
        } else if (jugada == "pares" || jugada == "juego") {
            fetch(`${path}/api/menuparjuego/${mesa.id_mesa}/${usuario.login}`)
                .then((res) => res.json())
                .then((res) => {
                    if (res.comprobacion) {
                        mostrarMenu(estado);
                    } else {
                        if (res.estado == "limpio") {
                            fetch(`${path}/api/paso/${mesa.id_mesa}/${usuario.login}`)
                                .then((res) => res.json())
                                .then((res) => {
                                    if (res.jugada == "mus") {
                                        socket.emit('showdown', res);
                                    } else {
                                        socket.emit('interaccion', res);
                                    }
                                });
                        } else {
                            fetch(`${path}/api/noquiero/${mesa.id_mesa}/${usuario.login}`)
                                .then((res) => res.json())
                                .then((res) => {
                                    if (typeof res.marcadores !== 'undefined') {
                                        socket.emit('actualizarMarcadores', res);
                                    }
                                    if (res.jugada == "mus") {
                                        socket.emit('showdown', res);// programar al final
                                    } else {
                                        socket.emit('interaccion', res);
                                    }
                                });
                        }
                    }
                });
        } else {
            mostrarMenu(estado);
        }
    }
}

function comprobarDescartes() {
    let descartes = "";
    let contador = 0;
    if ($("#carta1").hasClass('subirCarta')) {
        descartes += cartas[0].imagen + "+";//cartas[0] no cambia pasadas las manos
        contador++;
    }
    if ($("#carta2").hasClass('subirCarta')) {
        descartes += cartas[1].imagen + "+";
        contador++;
    }
    if ($("#carta3").hasClass('subirCarta')) {
        descartes += cartas[2].imagen + "+";
        contador++;
    }
    if ($("#carta4").hasClass('subirCarta')) {
        descartes += cartas[3].imagen + "+";
        contador++;
    }
    descartes += contador;
    return descartes;

}

function mostrarBocadillo(login, texto) {
    var contenido;
    var bocadillo
    if (login == compannero.login) {
        bocadillo = $("#bocatacompi");
        contenido = $("#textocompi");
    } else if (login == rivalDer.login) {
        bocadillo = $("#bocatarivalder");
        contenido = $("#textorivalder");
    } else if (login == rivalIzq.login) {
        bocadillo = $("#bocatarivalizq");
        contenido = $("#textorivalizq");
    }
    if (typeof contenido !== 'undefined') {
        bocadillo.removeClass("hidden");
        contenido.text(texto);
        setTimeout(() => {
            bocadillo.addClass("hidden");
        }, timeoutBocadillo);
    }
}

function actualizarMarcadores(data) {
    $("#puntosa").text(data[0].puntos);
    $("#juegosa").text(data[0].juegos);
    $("#vacasa").text(data[0].vacas);
    $("#puntosb").text(data[1].puntos);
    $("#juegosb").text(data[1].juegos);
    $("#vacasb").text(data[1].vacas);
}

function mostrarMensajesShowdown(data, contador) {
    setTimeout(() => {
        if (contador < data.contador) {
            $("#tapetes").text(data.textos[contador]);
            actualizarMarcadores(data.marcadores[contador]);
            contador++;
            mostrarMensajesShowdown(data, contador);
        }
    }, 3500 * contador);
}

// Rango Envites
$('#mas').click(function () {
    var num = Number($('#textoEnvidar').text())
    if (num < mesa.puntos) {
        var suma = num + 1;
        $('#textoEnvidar').text(suma);
        $('#envidar').text("Envido " + suma);
    }
});
$('#menos').click(function () {
    var num = Number($('#textoEnvidar').text())
    if (num > 2) {
        var suma = num - 1;
        $('#textoEnvidar').text(suma);
        $('#envidar').text("Envido " + suma);
    }
});
$('#haymas').click(function () {
    var num = Number($('#haytextoEnvidar').text())
    if (num < mesa.puntos) {
        var suma = num + 1;
        $('#haytextoEnvidar').text(suma);
        $('#hayenvidar').text("Envido " + suma);
    }
});
$('#haymenos').click(function () {
    var num = Number($('#haytextoEnvidar').text())
    if (num > 2) {
        var suma = num - 1;
        $('#haytextoEnvidar').text(suma);
        $('#hayenvidar').text("Envido " + suma);
    }
});

//Mostrar Cuadro Envites
$('#mostrarCuadro').hover(function () {
    $('#cuadroEnvites').show();
}, function () {
    $('#cuadroEnvites').hide();
});

//conectar socket
socket.on('connect', () => {
    usuario.socket_id = socket.id;
    usuario.id_mesa = mesa.id_mesa;
    socket.emit('partida', usuario);
});

//mantener lista usuarios conectados
socket.on('refrescarusuarios', (data) => {
    if (data.mesa == mesa.id_mesa) {
        hacerLista(data.users);
    }
});

//chat
mensaje.addEventListener("keydown", () => {
    key = event.keyCode;
    switch (key) {
        case 13:
            enviarTexto();
            break;
    }
});
enviar.addEventListener("click", enviarTexto);


socket.on('chatpartida:message', (data) => {
    if (mesa.id_mesa == data.mesa) {
        chat.innerHTML += `<div class="textoMensaje"><span>${data.nombre}:</span> ${data.mensaje}</div>`;
        $('#chat').scrollTop($('#chat').prop('scrollHeight'));
    }
});

//estadisticas
renderStats(usuario.login);
$('#compannero').click(() => {
    renderStats(compannero.login);
});
$('#rivalder').click(() => {
    renderStats(rivalDer.login);
});
$('#rivalizq').click(() => {
    renderStats(rivalIzq.login);
});

//control desconexion
socket.emit('interaccion', situacionEntrada);
colocarBaraja(situacionEntrada.mano);

//repartir
$('#repartir').click(() => {
    $('#repartir').addClass("hidden");
    socket.emit('repartir', mesa.id_mesa);
});

socket.on('repartir', (data) => {
    if (mesa.id_mesa == data.mesa_id) {
        hiddenReparto();
        ocultarBaraja();
        ocultarCartas();
        colocarReversos();
        colocarBaraja(data.mano);
        fetch(`${path}/api/cartas/${mesa.id_mesa}/${usuario.login}`)
            .then((res) => res.json())
            .then((res) => {
                cartas = res
                colocarMisCartas();
                repartir();
                setTimeout(() => {
                    actualizarDatosJugada(data);
                    comprobarYMostrarMenu(data.mano, data.turno, data.jugada, data.estado);
                }, 8000);
            });
    }

})

//juego
$('#mus').click(() => {
    $('#haymus').addClass("hidden");
    fetch(`${path}/api/mus/${mesa.id_mesa}/${usuario.login}`)
        .then((res) => res.json())
        .then((res) => {
            let datos = new Object();
            datos.texto = "Dame mus";
            datos.login = usuario.login;
            datos.mesa = mesa.id_mesa;
            socket.emit('mostrarbocadillo', datos);
            setTimeout(() => {
                socket.emit('interaccion', res);
            }, timeoutBocadillo);
        });
});

$('#descartar').click(() => {
    $('#divDescartes').addClass("hidden");
    let descartes = comprobarDescartes();
    desactivarSubidaCartas();
    fetch(`${path}/api/descartar/${mesa.id_mesa}/${usuario.login}/${descartes}`)
        .then((res) => res.json())
        .then((res) => {
            let datos = new Object();
            datos.texto = res.descartes > 0 ? `Dame ${res.descartes} cartas` : "Me quedo servido";
            datos.login = usuario.login;
            datos.mesa = mesa.id_mesa;
            cartas = res.cartas;
            delete res.cartas;
            $('#carta1').attr('src', `${path}/media/baraja/${cartas[0].imagen}`);
            $('#carta2').attr('src', `${path}/media/baraja/${cartas[1].imagen}`);
            $('#carta3').attr('src', `${path}/media/baraja/${cartas[2].imagen}`);
            $('#carta4').attr('src', `${path}/media/baraja/${cartas[3].imagen}`);
            socket.emit('mostrarbocadillo', datos);
            setTimeout(() => {
                socket.emit('interaccion', res);
            }, timeoutBocadillo);

        });
});

$('#nomus').click(() => {
    $('#haymus').addClass("hidden");
    fetch(`${path}/api/nomus/${mesa.id_mesa}/${usuario.login}`)
        .then((res) => res.json())
        .then((res) => {
            let datos = new Object();
            datos.texto = "No hay mus";
            datos.login = usuario.login;
            datos.mesa = mesa.id_mesa;
            socket.emit('mostrarbocadillo', datos);
            setTimeout(() => {
                socket.emit('interaccion', res);
            }, timeoutBocadillo);
        });
});

$('#paso').click(() => {
    $('#nohayEnvite').addClass("hidden");
    fetch(`${path}/api/paso/${mesa.id_mesa}/${usuario.login}`)
        .then((res) => res.json())
        .then((res) => {
            let datos = new Object();
            datos.texto = `Paso`;
            datos.login = usuario.login;
            datos.mesa = mesa.id_mesa;
            socket.emit('mostrarbocadillo', datos);
            setTimeout(() => {
                if (res.jugada == "mus") {
                    socket.emit('showdown', res);
                } else {
                    socket.emit('interaccion', res);
                }
            }, timeoutBocadillo);
        });
});

$('#envidar').click(() => {
    $('#nohayEnvite').addClass("hidden");
    let envite = $('#textoEnvidar').text();
    fetch(`${path}/api/envite/${mesa.id_mesa}/${usuario.login}/${envite}`)
        .then((res) => res.json())
        .then((res) => {
            let datos = new Object();
            datos.texto = `Envido ${envite}`;
            datos.login = usuario.login;
            datos.mesa = mesa.id_mesa;
            $('#textoEnvidar').text(2);
            $('#envidar').text("Envido 2");
            socket.emit('mostrarbocadillo', datos);
            setTimeout(() => {
                socket.emit('interaccion', res);
            }, timeoutBocadillo);
        });
});

$('#ordago').click(() => {
    $('#nohayEnvite').addClass("hidden");
    fetch(`${path}/api/ordago/${mesa.id_mesa}/${usuario.login}`)
        .then((res) => res.json())
        .then((res) => {
            let datos = new Object();
            datos.texto = `¡¡ORDAGO!!`;
            datos.login = usuario.login;
            datos.mesa = mesa.id_mesa;
            socket.emit('mostrarbocadillo', datos);
            setTimeout(() => {
                socket.emit('interaccion', res);
            }, timeoutBocadillo);
        });
});

$('#haynoquiero').click(() => {
    $('#hayEnvite').addClass("hidden");
    fetch(`${path}/api/noquiero/${mesa.id_mesa}/${usuario.login}`)
        .then((res) => res.json())
        .then((res) => {
            let datos = new Object();
            datos.texto = `No quiero`;
            datos.login = usuario.login;
            datos.mesa = mesa.id_mesa;
            socket.emit('mostrarbocadillo', datos);
            setTimeout(() => {
                if (typeof res.marcadores !== 'undefined') {
                    socket.emit('actualizarMarcadores', res);
                }
                if (res.jugada == "mus") {
                    socket.emit('showdown', res);
                } else {
                    socket.emit('interaccion', res);
                }
            }, timeoutBocadillo);
        });
});

$('#hayquiero').click(() => {
    $('#hayEnvite').addClass("hidden");
    fetch(`${path}/api/quiero/${mesa.id_mesa}/${usuario.login}`)
        .then((res) => res.json())
        .then((res) => {
            let datos = new Object();
            datos.texto = `Quiero`;
            datos.login = usuario.login;
            datos.mesa = mesa.id_mesa;
            socket.emit('mostrarbocadillo', datos);
            setTimeout(() => {
                if (res.jugada == "mus") {
                    socket.emit('showdown', res);
                } else {
                    socket.emit('interaccion', res);
                }
            }, timeoutBocadillo);
        });
});

$('#hayenvidar').click(() => {
    $('#hayEnvite').addClass("hidden");
    let envite = $('#haytextoEnvidar').text();
    fetch(`${path}/api/reenvite/${mesa.id_mesa}/${usuario.login}/${envite}`)
        .then((res) => res.json())
        .then((res) => {
            let datos = new Object();
            datos.texto = `${envite} más!!`;
            datos.login = usuario.login;
            datos.mesa = mesa.id_mesa;
            $('#haytextoEnvidar').text(2);
            $('#hayenvidar').text("Envido 2");
            socket.emit('mostrarbocadillo', datos);
            setTimeout(() => {
                socket.emit('interaccion', res);
            }, timeoutBocadillo);
        });
});

$('#hayordago').click(() => {
    $('#hayEnvite').addClass("hidden");
    fetch(`${path}/api/ordago/${mesa.id_mesa}/${usuario.login}`)
        .then((res) => res.json())
        .then((res) => {
            let datos = new Object();
            datos.texto = `¡¡ORDAGO!!`;
            datos.login = usuario.login;
            datos.mesa = mesa.id_mesa;
            socket.emit('mostrarbocadillo', datos);
            setTimeout(() => {
                socket.emit('interaccion', res);
            }, timeoutBocadillo);
        });
});

$('#quiero').click(() => {
    $('#responderOrdago').addClass("hidden");
    let datos = new Object();
    datos.login = usuario.login;
    datos.texto = "Quiero";
    datos.mesa = mesa.id_mesa;
    socket.emit('mostrarbocadillo', datos);
    setTimeout(() => {
        let datos2 = new Object();
        datos2.mesa_id = mesa.id_mesa;
        datos2.estado = "ordago";
        socket.emit('showdown', datos2);
    }, timeoutBocadillo);
});

$('#noquiero').click(() => {
    $('#responderOrdago').addClass("hidden");
    fetch(`${path}/api/noquiero/${mesa.id_mesa}/${usuario.login}`)
        .then((res) => res.json())
        .then((res) => {
            let datos = new Object();
            datos.texto = `No quiero`;
            datos.login = usuario.login;
            datos.mesa = mesa.id_mesa;
            socket.emit('mostrarbocadillo', datos);
            setTimeout(() => {
                if (typeof res.marcadores !== 'undefined') {
                    socket.emit('actualizarMarcadores', res);
                }
                if (res.jugada == "mus") {
                    socket.emit('showdown', res);
                } else {
                    socket.emit('interaccion', res);
                }
            }, timeoutBocadillo);
        });
});



socket.on('actualizarMarcadores', (data) => {
    if (data.mesa_id == mesa.id_mesa) {
        actualizarMarcadores(data.marcadores);
    }
})

socket.on('interaccion', (data) => {
    if (data.mesa_id == mesa.id_mesa) {
        if (data.turno == 0) {
            $("#tapetej").text(data.jugada.toUpperCase());
        }

        if (data.estado == "comprobando" && usuario.posicion == (data.mano + data.turno) % 4) {
            fetch(`${path}/api/paresojuego/${mesa.id_mesa}/${usuario.login}`)
                .then((res) => res.json())
                .then((res) => {
                    let datos = new Object();
                    datos.texto = res.comprobacion ? "Tengo" : "No tengo";
                    datos.login = usuario.login;
                    datos.mesa = mesa.id_mesa;
                    socket.emit('mostrarbocadillo', datos);
                    setTimeout(() => {

                        if (res.jugada == "mus") {
                            socket.emit('showdown', res);
                        } else {
                            socket.emit('interaccion', res);
                        }
                    }, timeoutBocadillo);
                });
        } else {
            comprobarYMostrarMenu(data.mano, data.turno, data.jugada, data.estado);
        }
        actualizarDatosJugada(data);
    }
});

//bocadillos
socket.on('mostrarbocadillo', (data) => {
    if (data.mesa == mesa.id_mesa) {
        if (data.login != usuario.login) {
            mostrarBocadillo(data.login, data.texto);
        }
    }

})

//tapete
socket.on('tapete', (data) => {
    if (data == mesa.id_mesa)
        $("#tapetesdiv").addClass("hidden");
    $("#tapetej").removeClass("hidden");
})

//levantar cartas
function levantarCartas(usu, cartas) {
    for (let i = 1; i <= 4; i++) {
        $(`#${usu}${i}`).attr('src', `${path}/media/baraja/${cartas[i - 1].imagen}`);
    }
}

socket.on('showdown:levantarcartas', (data) => {
    if (data[4] == mesa.id_mesa) {
        for (let i = 0; i < 4; i++) {
            if (compannero.posicion == i) {
                levantarCartas("compi", data[i]);
            }
            if (rivalDer.posicion == i) {
                levantarCartas("rivalder", data[i]);
            }
            if (rivalIzq.posicion == i) {
                levantarCartas("rivalizq", data[i]);
            }
        }
    }

})

socket.on('showdown:ordago', (data) => {
    if (data.mesa_id == mesa.id_mesa) {
        $("#tapetesdiv").removeClass("hidden");
        $("#tapetes").text(data.texto);
    }

})

socket.on('showdown', (data) => {
    if (data.mesa == mesa.id_mesa) {
        $("#tapetesdiv").removeClass("hidden");
        $("#tapetej").removeClass("hidden");        
        mostrarMensajesShowdown(data, 0);
    }

})

//Abandonar partida
$("#salir").click(() => {
    if (window.confirm("¿Quieres abandonar la partida?")) {
        console.log("he aceptado");
        fetch(`${path}/api/abandonarmesa/${mesa.id_mesa}/${usuario.login}`)
            .then((res) => res.text())
            .then((res) => {
                console.log("he pasado el fetch");
                if (res == 'ok') {
                    socket.emit('salirdemesa', mesa.id_mesa);
                } else {
                    console.log(res);
                }
                
            });
    }
})

socket.on('salirdemesa', (data) => {
    if (mesa.id_mesa == data) {
        window.location = `${path}`;
    }
})


