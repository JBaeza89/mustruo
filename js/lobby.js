//variables
const socket = io('http://localhost:3300');
var chat = document.getElementById("chat");
var usuarios = document.getElementById("usuariosonline");
var enviar = document.getElementById("enviarmensaje");
var mensaje = document.getElementById("mensaje");
var semana = document.getElementById("semana");
var mes = document.getElementById("mes");
var anno = document.getElementById("anno");
var clasificacion = document.getElementById("clasificacion");
var mesas = document.getElementById("mesas");
var btnlevantarse = document.getElementById("levantarse");
//varibles solo para registrados;
if (usuario != "anonimo") {
    var nuevaMesa = document.getElementById("nuevamesa");
}

//functions
function comprobarSesion() {
    var login = document.getElementById("login").value;
    var contrasenna = document.getElementById("contrasenna").value;
    if (login.length > 3 && contrasenna.length > 3) {
        fetch(`${URL_PATH}/api/comprobarSesion/${login}/${contrasenna}`)
            .then(function (response) {
                return response.text()
            }).then(function (datos) {
                if (datos == "si") {
                    alert('Compruebe de nuevo su login y/o contraseña')
                } else {
                    location.replace(URL_PATH + "/");
                }
            })
    } else {
        alert('Compruebe de nuevo su login y/o contraseña')
    }
}

function enviarTexto() {
    if (mensaje.value != "") {
        socket.emit("chatlobby:message", {
            "mensaje": mensaje.value,
            "nombre": usuario.login
        })
        mensaje.value = "";
    }
}

function renderStats(login) {
    fetch(`${path}/api/estadisticas/${login}`)
        .then((res) => res.json())
        .then((res) => {
            $("#usuestadisticas").text(login).attr('href', `${path}/perfil/${login}`);
            $("#juegosJugados").text(res.juegos_jugados);
            $("#juegosGanados").text(res.juegos_ganados);
            $("#vacasJugadas").text(res.vacas_jugadas);
            $("#vacasGanadas").text(res.vacas_ganadas);
            $("#abandonos").text(res.abandonos);
        });
}

function hacerLista(zona, data) {
    var container = document.createElement("div");
    var previo = "";
    for (let i = 0; i < data.length; i++) {
        let login = data[i].login;
        let puntos = data[i].puntos;
        if (login != previo) {
            let li = document.createElement("li");
            let div = document.createElement("div");
            if (zona == clasificacion) {
                div.setAttribute("class", `rank rank${i + 1}`);
                switch (i) {
                    case 0:
                        div.innerHTML = `<span>${i + 1}. ${login}</span><div class='puntos'><img src='${path}/media/oro.png'><span class="puntoSpan centrarTexto">${puntos}pts</span></div>`;
                        break;
                    case 1:
                        div.innerHTML = `<span>${i + 1}. ${login}</span><div class='puntos'><img src='${path}/media/plata.png'><span class="puntoSpan centrarTexto">${puntos}pts</span></div>`;
                        break;
                    case 2:
                        div.innerHTML = `<span>${i + 1}. ${login}</span><div class='puntos'><img src='${path}/media/bronce.png'><span class="puntoSpan centrarTexto">${puntos}pts</span></div>`;
                        break;
                    default:
                        div.innerHTML = `<span>${i + 1}. ${login}</span><div class='puntos'><span class="puntoSpan centrarTexto">${puntos}pts</span></div>`;
                        break;
                }
                container.append(div);
                div.addEventListener("click", () => {
                    renderStats(login);
                });
            } else {
                li.innerHTML = `<span>${login}</span>`;
                container.append(li);
                li.addEventListener("click", () => {
                    renderStats(login);
                });
            }
            previo = login;
        }
    }
    zona.innerHTML = "";
    zona.append(container);
}

function levantarse() {
    if (usuario.estaSentado) {
        fetch(`${path}/api/levantarse/${usuario.mesa_id}/${usuario.posicion}/${usuario.login}`)
            .then((res) => res.text())
            .then((res) => {
                console.log(res);
                if (res == "ok") {
                    socket.emit('actualizarmesas');
                } else {
                    window.alert("falla levantarse");
                }
            })
    }
}

function sentarse(id, i) {
    fetch(`${path}/api/sentarse/${id}/${i}/${usuario.login}`)
        .then((res) => res.text())
        .then((res) => {
            console.log(res);
            if (res != "nook") {
                socket.emit("actualizarmesas");
            } else {
                window.alert("falla sentarse");
            }
        })
}

function crearAsientos(id, mesa, i) {
    let asiento = document.createElement("div");
    asiento.setAttribute("class", `posicion${i} asiento`);
    let img = document.createElement("img");
    let span = document.createElement("span");
    span.setAttribute("class", "centrarTexto")
    span.innerText = "";
    img.setAttribute("src", `${path}/media/asientolibre.PNG`);//poner imagen y adaptar ruta    
    img.addEventListener("click", () => {
        if (usuario != "anonimo") {
            if (img.getAttribute("src") == `${path}/media/asientolibre.PNG`) {
                if (!usuario.estaSentado) {
                    sentarse(id, i);
                } else {
                    window.alert("Ya estas sentado en otro lugar");
                }
            } else {
                renderStats(span.innerText);
            }
        } else {
            if (img.getAttribute("src") == `${path}/media/asientolibre.PNG`) {
                window.alert("Tienes que estar conectado");
            } else {
                renderStats(span.innerText);
            }
        }
    });
    asiento.append(img);
    asiento.append(span);
    mesa.append(asiento);
}

function renderUsuarios(id, mesa) {
    fetch(`${path}/api/usuariosmesa/${id}`)
        .then((res) => res.json())
        .then((res) => {
            for (let i = 0; i < 4; i++) {
                crearAsientos(id, mesa, i);
            }
            for (let usu of res) {
                mesa.childNodes[usu.posicion].setAttribute("id", "bloqueUsu");
                let asiento = mesa.childNodes[usu.posicion];
                asiento.childNodes[0].setAttribute("src", `${path}/media/avatares/${usu.imagen}`);
                asiento.childNodes[1].innerText = usu.login;
            }
            if (usuario.mesa_id == id && usuario.listo) {
                var listo = document.createElement("button");
                listo.setAttribute("class", "listo boton-marron");
                listo.innerText = "Listo!";
                mesa.append(listo);
                listo.addEventListener("click", () => {
                    fetch(`${path}/api/empezarpartida/${usuario.mesa_id}`)
                        .then((res) => res.text())
                        .then((res) => {
                            if (res == "ok") {
                                socket.emit("empezarpartida", usuario.mesa_id);
                            }
                        })

                })
            }

        })

}

function renderMesa(mesa, container) {
    var div = document.createElement("div");
    div.id = `mesa${mesa.id_mesa}`;
    div.setAttribute("class", "itemMesa");
    renderUsuarios(mesa.id_mesa, div);
    container.append(div);
}

function actualizarMesas() {
    fetch(`${path}/api/estadousuario/${usuario.login}`)
        .then((res) => res.json())
        .then((res) => {
            usuario.estaSentado = res.posicion >= 0;
            if (usuario.estaSentado) {
                usuario.posicion = res.posicion;
                usuario.mesa_id = res.mesa_id;
                usuario.listo = usuario.posicion == 0 && res.jugadores_mesa == 4;
            } else {
                usuario.posicion = -1;
                usuario.mesa_id = -1;
            }
            fetch(`${path}/api/mesas`)
                .then((res) => res.json())
                .then((res) => {
                    var container = document.createElement("div");
                    for (let mesa of res) {
                        renderMesa(mesa, container);
                    }
                    mesas.innerHTML = "";
                    mesas.append(container);
                    if (usuario.estaSentado) {
                        btnlevantarse.setAttribute("class", "dejarMesa");
                        nuevaMesa.setAttribute("class", "hidden");
                    } else {
                        btnlevantarse.setAttribute("class", "hidden");
                        nuevaMesa.setAttribute("class", "nuevaMesa");
                    }
                })

        })
}

function renderRanking(tipo) {
    fetch(`${path}/api/ranking/${tipo}`)
        .then((res) => res.json())
        .then((res) => {
            hacerLista(clasificacion, res);
        })
}

//rankings

renderRanking(0);


semana.addEventListener("click", () => {
    $('#pestannasRanking').text('de la semana');
    renderRanking(0);
})

mes.addEventListener("click", () => {
    $('#pestannasRanking').text('del mes');
    renderRanking(1);
})

anno.addEventListener("click", () => {
    $('#pestannasRanking').text('del año');
    renderRanking(2);
})

//conectar socket
socket.on('connect', () => {
    usuario.socketid = socket.id;
    socket.emit('lobby', usuario);
});

//mantener lista usuarios conectados
socket.on('refrescarusuarios', (data) => {
    hacerLista(usuarios, data);

});

//estadisticas
if (usuario != "anonimo") {
    renderStats(usuario.login)
}

//chat
if (usuario != "anonimo") {
    enviar.addEventListener("click", enviarTexto);
    mensaje.addEventListener("keydown", () => {
        key = event.keyCode;
        switch (key) {
            case 13:
                enviarTexto();
                break;
        }
    });
}

socket.on('chatlobby:message', (data) => {
    chat.innerHTML += `<div class="textoMensaje"><span>${data.nombre}:</span> ${data.mensaje}</div>`;
    $('#chat').scrollTop($('#chat').prop('scrollHeight'));
});

//crear mesa y levantarse de mesa
if (usuario != "anonimo") {
    nuevaMesa.addEventListener("click", () => {
        if (usuario.estaSentado) {
            window.alert("Ya estas sentado en una mesa");
        } else {
            $('#nuevamesamodal').modal('show');
            var botpr = document.getElementById("botpr");
            botpr.addEventListener("click", () => {
                let priv = document.getElementById("privacidad");
                let clave = document.getElementById("clave")
                if (botpr.innerText == "Privada") {
                    botpr.setAttribute("class", "btn btn-success");
                    botpr.innerText = "Publica";
                    clave.setAttribute("disabled", "disabled");
                    priv.value = "1";
                } else {
                    botpr.setAttribute("class", "btn btn-danger");
                    botpr.innerText = "Privada";
                    clave.removeAttribute("disabled");
                    priv.value = "0";
                }
            })
        }
    })
    btnlevantarse.addEventListener("click", levantarse);
}

//actualizar mesas
if (arg == "mc") {
    socket.emit("actualizarmesas");
} else {
    actualizarMesas();
}
socket.on('actualizarmesas', () => {
    actualizarMesas();
})

//empezar partida
socket.on("empezarpartida", (data) => {
    if (usuario.mesa_id == data) {
        window.location = `${path}/mesa/${data}`;
    }
})
//Comprabar Sesion
$('#comprobarSesion').click(comprobarSesion);
document.getElementById('login').addEventListener("keydown", () => {
    key = event.keyCode;
        switch (key) {
            case 13:
                comprobarSesion();
                break;
        }
})
document.getElementById('contrasenna').addEventListener("keydown", () => {
    key = event.keyCode;
        switch (key) {
            case 13:
                comprobarSesion();
                break;
        }
})

