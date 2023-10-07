# ViewRenderVue

Esta é uma extensão da classe [view](https://github.com/php-elegance/server/blob/main/.doc/view.md) para suporte a vuejs

### Considerações view VueJS

Sempre que uma aplicação VUEJS for carregada, ela aciona automaticamente o front. Todos o processo de carregamento do Vue e montagem da aplicação é feita forma automatizada.

As views do tipo VUE são convertidas em objetos javascript e tags css. O conteúdo css e scss é unificado, compilado e minificado. O conteúdo do componente é convertido para que possa ser lido via [front](https://github.com/php-elegance/front/blob/main/.doc/front.md).

Em geral, escreva o componente vue normalmente

    <template >
        <h1>{{ name }}</h1>
    </template>
    <script>
        export default {
            data() {
                return {
                    name: "Pedro",
                };
            },
        };
    </script>
    <style>
        h1{ color:blue; }
    </style>

> Como um componente VUE é essencialmente javascript, a dica sobre tratamento de tipos deve ser respeitada dento de um arquivo .vue

Ao chamar uma view VUE.js via tag prepare é preciso informar o ID da div onde o componte será montado.

    [#view:myapp.vue,app] // O componente mysql.vue será montado na div #app

Para importar componentes vue dentro de um componente vocẽ utilizaria o recurso **import** do javascript. Ao invez disso, utilize chamadas de **view**

O componte importado já vai estar pronto para se utilizado. Informe o nome do componente como segundo parametro, caso não informe um nome, o nome do arquivo será utilizado.

    <template >
        <div>
            <MENU></MENU>
            <FOOTER></FOOTER>
        </div>
    </template>
    <script>
        // [#view:menu.vue,menu]
        // [#view:Footer.vue]
        export default {};
    </script>

> Esca classe não tem a intenção de interpretar ou renderizar um compoente. Ela organiza e envia o componente para ser renderizado no lado do cliente.


