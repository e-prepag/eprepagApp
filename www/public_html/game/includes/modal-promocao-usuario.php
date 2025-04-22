<style>
.modal-promocao {
	font-size: 62.5%;
	border-radius: 27px;
    box-shadow: 0px 0px 4px 0px #000;
    color: #0d0d0d;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 999999999999;
	opacity: 0;
}

.modal-promocao-body {
	padding: 10px;
    display: flex;
    flex-direction: column;
	height: 70vh;
	width: 70vw;
    align-items: center;
    justify-content: space-evenly;
    background: #f3f3f3;
    border-radius: 27px;
    flex-wrap: wrap;
}

.modal-promocao-body p {
    margin: 0;
    font-size: 2em;
    text-transform: uppercase;
    line-height: 1.3em;
    text-align: center;
}

span.destaque-secundario {
    font-weight: bold;
    color: #0eb725;
}

span.destaque-principal {
    color: #268fbd;
    font-weight: bold;
    font-size: 1.7em;
}

span.foguete {
    font-size: 1.3em;
}

button.btn-promocao {
	margin: 0 auto;
    background: #0eb725;
	width: 7em;
    height: 3em;
    font-weight: bold;
    border-radius: 18px;
    font-size: 1.8em;
    border: unset;
    box-shadow: 0 0 4px 0 #77ff89;
	transition: 0.6s all ease;
}

button.btn-promocao:hover {
	background: #268fbd;
	color: #111;
    box-shadow: 0 0 4px 0 #71cef7;
	transition: 0.6s all ease;
}

button.btn-promocao:focus {
	background: #71cef7;
	transition: 0.6s all ease;
}

a.link-opniao {
	text-decoration: none;
	background: #009b4a;
    color: #fff;
    padding: 7px 13px;
    border-radius: 15px;
	transition: 0.3s ease-in-out;
}

a.link-opniao:hover {
	background: #266c8a;
    color: #fff;
    transition: 0.3s ease-in-out;
}

.show-content-modal-promocao p,
.show-content-modal-promocao button {
	animation: show-content-modal 2.7s ease-in-out forwards;
}

@keyframes show-modal {
	0% {
		opacity: 0;
	}
	100% {
		opacity: 1;		
	}
}

@keyframes hide-modal {
	0% {
		opacity: 1;
	}
	50% {
		opacity: 0;
	}
	100% {
		display: none;
	}
}

@media screen and (max-width: 800px) {
	.modal-promocao-body p {
		font-size: 1.3em;
		
		text-align: center;
	}
	
	button.btn-promocao {
		font-size: 1.3em;
	}
}

</style>

<div class="modal-promocao">
	<div class="modal-promocao-body">
	
		<p>Esse é um convite exclusivo para você desfrutar uma </p>
		
		<p><span class="destaque-secundario">nova experiência digital</span> desenvolvida pela E-Prepag e ainda </p>
		
		<p><span class="destaque-principal">ganhar 1 Pin Free Fire da Garena</span></p>
		
		<p>Abra seu e-mail e saiba como participar!</p>
		
		<p>Corra, pois é por tempo limitado!</p>
		
		<p><span class="foguete">&#128640; &#128640; &#128640;</span></p>
		
		<button class="btn btn-promocao fecha-modal">OK</button>
	</div>
</div>
<script>

	const modal_promocao = document.querySelector('.modal-promocao');
	const fecha_modal = document.querySelector('.fecha-modal');
	
	window.addEventListener("load", (event) => {
		setTimeout(() => {
			modal_promocao.style.animation = 'show-modal 1s forwards';
		}, 1000);
	});

	fecha_modal.addEventListener('click', (event) => {
		modal_promocao.style.animation = 'hide-modal 1s forwards';
	});
	
</script>