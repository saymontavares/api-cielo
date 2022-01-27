<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamentos</title>

    <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">
    <link href="{{ url('css/style.css') }}" rel="stylesheet">

    <script src="https://unpkg.com/vue@next"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <div class="min-w-screen min-h-screen bg-gray-200 flex items-center justify-center px-5 pb-10 pt-16" id="app">
        <form @submit.prevent="sendPayment()" class="w-full mx-auto rounded-lg bg-white shadow-lg p-5 text-gray-700" style="max-width: 600px" novalidate>
            <div class="w-full pt-1 pb-5">
                <div class="bg-green-500 text-white overflow-hidden rounded-full w-20 h-20 -mt-16 mx-auto shadow-lg flex justify-center items-center">
                    <i class="mdi mdi-credit-card-outline text-3xl"></i>
                </div>
            </div>
            <div class="mb-10">
                <h1 class="text-center font-bold text-xl uppercase">Informações para pagamento</h1>
                <h3 class="text-center">Valor: R$ 0,10</h3>
            </div>
            <div class="mb-3 flex justify-center -mx-2">
                <div class="px-2">
                    <label for="type1" class="flex items-center cursor-pointer">
                        <input type="radio" class="form-radio mr-2 h-5 w-5 text-green-500" name="type" id="type1" value="C" v-model="form.tipo" checked>
                        <!-- <img src="https://leadershipmemphis.org/wp-content/uploads/2020/08/780370.png" class="h-8 ml-3"> -->
                        CRÉDITO
                    </label>
                </div>
                <div class="px-2">
                    <label for="type2" class="flex items-center cursor-pointer">
                        <input type="radio" class="form-radio mr-2 h-5 w-5 text-green-500" name="type" id="type2" value="D" v-model="form.tipo">
                        <!-- <img src="https://www.sketchappsources.com/resources/source-image/PayPalCard.png" class="h-8 ml-3"> -->
                        DÉBITO
                    </label>
                </div>
            </div>
            <div class="mb-3">
                <ul v-for="error in errors">
                    <li class="flex items-center py-1 text-center" v-for="erro in error">
                        <div class="rounded-full p-1 fill-current bg-red-200 text-red-700">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <span class="font-medium text-sm ml-3 text-red-700">@{{ erro }}</span>
                    </li>
                </ul>
            </div>
            <div class="mb-3">
                <label class="font-bold text-sm mb-2 ml-1">Nome Completo</label>
                <div>
                    <input class="w-full px-3 py-2 mb-1 border-2 border-gray-200 rounded-md focus:outline-none focus:border-green-500 transition-colors" placeholder="Seu Nome Completo" v-model="form.nome" type="text"/>
                </div>
            </div>
            <div class="mb-3">
                <label class="font-bold text-sm mb-2 ml-1">Nome Impresso no Cartão</label>
                <div>
                    <input class="w-full px-3 py-2 mb-1 border-2 border-gray-200 rounded-md focus:outline-none focus:border-green-500 transition-colors" placeholder="Nome Impresso" v-model="form.cartao.nome" type="text"/>
                </div>
            </div>
            <div class="mb-3">
                <label class="font-bold text-sm mb-2 ml-1">Número do Cartão</label>
                <div>
                    <input class="w-full px-3 py-2 mb-1 border-2 border-gray-200 rounded-md focus:outline-none focus:border-green-500 transition-colors" placeholder="0000 0000 0000 0000" v-model="form.cartao.numero" type="text"/>
                </div>
            </div>
            <div class="mb-3" v-show="form.tipo == 'C'">
                <label class="font-bold text-sm mb-2 ml-1">Parcelas</label>
                <div>
                    <select class="form-select w-full px-3 py-2 mb-1 border-2 border-gray-200 rounded-md focus:outline-none focus:border-green-500 transition-colors cursor-pointer" v-model="form.parcelas">
                        <option value="1">01</option>
                        <option value="2">02</option>
                        <option value="3">03</option>
                        <option value="4">04</option>
                        <option value="5">05</option>
                        <option value="6">06</option>
                        <option value="7">07</option>
                        <option value="8">08</option>
                        <option value="9">09</option>
                        <option value="10">10</option>
                        <option value="11">11</option>
                        <option value="12">12</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="font-bold text-sm mb-2 ml-1">Bandeira</label>
                <div>
                    <select class="form-select w-full px-3 py-2 mb-1 border-2 border-gray-200 rounded-md focus:outline-none focus:border-green-500 transition-colors cursor-pointer" v-model="form.cartao.bandeira">
                        <option value="visa">Visa</option>
                        <option value="mastercard">Master Card</option>
                        <option v-if="form.tipo == 'C'" value="amex" >Amex</option>
                        <option v-if="form.tipo == 'C'" value="elo" >Elo</option>
                        <option v-if="form.tipo == 'C'" value="diners" >Diners</option>
                        <option v-if="form.tipo == 'C'" value="discover" >Discover</option>
                        <option v-if="form.tipo == 'C'" value="jcb" >Jcb</option>
                        <option v-if="form.tipo == 'C'" value="aura" >Aura</option>
                    </select>
                </div>
            </div>
            <div class="mb-3 -mx-2 flex items-end">
                <div class="px-2 w-1/2">
                    <label class="font-bold text-sm mb-2 ml-1">Validade</label>
                    <div>
                        <select class="form-select w-full px-3 py-2 mb-1 border-2 border-gray-200 rounded-md focus:outline-none focus:border-green-500 transition-colors cursor-pointer" v-model="form.cartao.mes">
                            <option value="01">01 - Janeiro</option>
                            <option value="02">02 - Fevereiro</option>
                            <option value="03">03 - Março</option>
                            <option value="04">04 - Abril</option>
                            <option value="05">05 - Maio</option>
                            <option value="06">06 - Junho</option>
                            <option value="07">07 - Julho</option>
                            <option value="08">08 - Agosto</option>
                            <option value="09">09 - Setembro</option>
                            <option value="10">10 - Outubro</option>
                            <option value="11">11 - Novembro</option>
                            <option value="12">12 - Dezembro</option>
                        </select>
                    </div>
                </div>
                <div class="px-2 w-1/2">
                    <select class="form-select w-full px-3 py-2 mb-1 border-2 border-gray-200 rounded-md focus:outline-none focus:border-green-500 transition-colors cursor-pointer" v-model="form.cartao.ano">
                        <option value="2021">2021</option>
                        <option value="2022">2022</option>
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                        <option value="2027">2027</option>
                        <option value="2028">2028</option>
                        <option value="2029">2029</option>
                        <option value="2029">2030</option>
                        <option value="2029">2031</option>
                    </select>
                </div>
            </div>
            <div class="mb-10">
                <label class="font-bold text-sm mb-2 ml-1">CVV</label>
                <div>
                    <input class="w-32 px-3 py-2 mb-1 border-2 border-gray-200 rounded-md focus:outline-none focus:border-green-500 transition-colors" placeholder="000" v-model="form.cartao.cvv" type="text"/>
                </div>
            </div>
            <div>
                <button type="submit" class="block w-full max-w-xs mx-auto bg-green-500 hover:bg-green-700 focus:bg-green-700 text-white rounded-lg px-3 py-3 font-semibold"><i class="mdi mdi-lock-outline mr-1"></i> PAGAR</button>
            </div>
            <div class="mt-5 text-center">
                <hr>
                <small class="text-gray-500">&copy; Desenvolvimento SATC - {{ date('Y') }}</small>
            </div>
        </form>
    </div>

    <script>
        Vue.createApp({
            created() {
                this.form.ref = `${this.getRandomInt(0, 999999)}`
            },
            data() {
                return {
                    form: {
                        ref: 0,
                        tipo: 'C',
                        valor: 0.10,
                        parcelas: 1,
                        cartao: {
                            mes: '01',
                            ano: 2021,
                            bandeira: 'visa'
                        }
                    },
                    errors: []
                }
            },
            methods: {
                getRandomInt(min, max) {
                    min = Math.ceil(min);
                    max = Math.floor(max);
                    return Math.floor(Math.random() * (max - min)) + min;
                },
                sendPayment() {
                    this.errors = []
                    let url =  this.form.tipo == 'C' ? `${location.href}card/credit`
                                                     : `${location.href}card/debit`
                    let config = {
                        method: 'post',
                        url: url,
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        data : JSON.stringify({
                            ref: this.form.ref,
                            nome: this.form.nome,
                            valor: this.form.valor,
                            parcelas: this.form.parcelas,
                            cartao: {
                                bandeira: this.form.cartao.bandeira,
                                numero: this.form.cartao.numero,
                                validade: `${this.form.cartao.mes}/${this.form.cartao.ano}`,
                                cvv: this.form.cartao.cvv,
                                nome: this.form.cartao.nome
                            }
                        })
                    };

                    axios(config)
                    .then(response => {
                        let data = JSON.stringify(response.data)
                        if(data.authenticationUrl) window.location.href = data.authenticationUrl
                        Swal.fire(
                            'Bom trabalho!',
                            'Tudo ocorreu bem por aqui 😄',
                            'success'
                        )
                    })
                    .catch(error => {
                        if (error.response.status == 422) {
                            this.errors = error.response.data
                        } else if (error.response.status == 400) {
                            this.errors = {
                                card: [error.response.data.mensagemret]
                            }
                        }
                    })
                }
            }
        }).mount('#app')
    </script>

</body>
</html>