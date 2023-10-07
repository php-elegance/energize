# Front

Encapsula a resposta em uma página html integrada com front.js

## Alterando dados da página

Você pode alterar os dados da página sem precisar alerar o arquivo base.html. Para isso, use os metodos abaixo.

**title**: Define o titulo da página no navegador

    Front::title('value');

    [#head.title]

**favicon**: Define o favicon da página no navegador

    Front::favicon('value');

    [#head.favicon]

**description**: Define o valor da tag description

    Front::description('value');

    [#head.description]

**head**: Define outros parametros para o head

    Front::head('name','value');

    [#head.name]

### Alterar layouts

Você deve criar o layout na pasta **front/layout**. Um layout é uma view que será utilizada para encapsular o conteúdo
Para alterar o layout de uma resposta, utilize o metodod **Front::layout('layoutName')**

    Front::layout('default');

### Grupos de layout

Por padrão, layouts são estaticos e, a não ser que o arquivo mude, se mantem inalterados durante a navegação.
Você pode desativar este comportamento com o metodo metodod **Front::layoutGroup('groupName')**

    Front::layoutGroup('groupName');

Uma requisições que utilizam grupos diferentes sempre terão o layout atualizado.

### Conteúdo Aside

Para adicionar conteúdo aside ao frontend, utilize o metodo **Front::aside('asideHtml')**

    Front::aside('asideHtml','asideName');

O conteúdo aside vai ser inserido dentro da tag prepade **[#aside.asideName]**.

### Funcionamento

Sempre que uma resposta passar pela middleware **elegance.front** ela será tratada como uma página de frontend e vai respeitar a seguintes regras.

  - Sempre que uma página for acessada diretamente ela via ser entregue em HTML
  - Sempre que uma página for chamada dinamicamente, ela vai retornar um JSON com o conteúdo
  - Sempre que uma página utiliar um layout diferente do layout atual, ela vai retornar um JSON com o layout e o conteúdo

 > Essa implementação não precisa ser feita. tudo já está aplicado e instalado no arquivo front.js

---

# FRONT.JS

Biblioteca javascript para dinamismo em projetos Elegance

O arquivo javascript pode ser encontrado em **library/assets/script/front.js**

### Inicilização

Utilize o comando para fazer a instalação do front

    php front install.front

Este comando vai instalar todos os arquivos nescessarios. 

### Funcionamento

O front funciona como uma ponte entre o frontend e o backend de seu projeto. É ativado automaticamente sempre a reposta tiver a middleware **elegance.front**.

### Load Dinamico

Esta função impede que a página seja recarregada sem motivo, o front detecta a base, estrutura e conteúdo utilizado e modifica apenas o que for nescessario.

Para evitar que o front tente utilizar este recurso em links externos, deve-se injetar a propriedade **front** em qualquer elemento HTML que contenha a propriedade **href**. Elementos sem essa propriede não serão tratados como load dinamico.

    <a href='url'>meu link</a> // Este link vai recarregar a página
    <a front href='url'>meu link</a> // Este link vai carregar as partes modificadas da página.

Para utilizar o load dinamico dentro de um aplicativo VueJs, utilize o metodo exposto **front.go**

    <script>
        export default :{
            methods: {
                link() {
                    front.go("url");
                },
            },
        }
    </script>

### __page

Devido a forma como o javascript funciona, não é possivel utilizar funções globais em chamadas dinamicas. Se precisar criar metodos ou objetos, adicione-os ao objeto **front.action**

    function myFunction(){...} //comportamento inprevisivel
    front.action.myFunction = (){...} //funciona como esperado

### Submit automático

Esta função adiciona uma forma simplificada de realizar submits internos em seu projeto. Para que funcione, adicione a propriedade **front** em uma tag **form**

    <form front>
        ...
    </form>

O formulário será automaticamente submetido utilizando as propriedes **method** e **action** da tag. O valor padrão para **action** é a URL atual. O valor padrão para **method** é **post**. A resposta da chamada será retornada ao fomulário que pode se comportar de 3 formas.

**Tratamento de respostas**
Pode-se adicionar a tag do formulário duas propriedes de tratamento. A propriedade **onsuccess** deve conter a chamada para tratemto de chamadas bem sucedidas (status < 300). A propriedade **onerror** deve conter a chamada para tratemto de chamadas com erro (status >=300). O front vai executar a chamada apropriada para cada requisição.

    <form front onsuccess="page.form_success" onerror="page.form_error">
    </form>
    <script>
        page.form_success = (response) {
            console.log("success", response);
        }
        page.form_error = (response) {
            console.log("error", response);
        }
    </script>

**Auto alert**
Se o formulário não informar a forma de tratamento da resposta e tiver uma tag com a classe **__alert**, a resposta será exibida dentro desta tag.

    <form front>
        <span class='__alert'></span>
    </form>

A resposta será inserida no seguingue padrão

    <span class='__alert'>
        <span class='sts_${resp.info.status} ${spanClass}'>
            <span>
                ${resp.info.message}
            </span>
            <span>
                ${resp.info.description}
            </span>
        </span>
    </span>

**Sem ação**
Se o formulário não informar a forma de tratamento da resposta e não tiver uma tag com a classe **__alert**, a resposta será descartada.

Para utilizar o load dinamico dentro de um aplicativo VueJs, utilize o metodo exposto do **front.submit**

    <script>
        export default :{
            methods: {
                async submit() {
                    let response = await front.submit(url, method, data = {});
                },
            },
        }
    </script>

> Chamar o submit via metodo exposto no vuejs vai sempre retornar a resposta em forma de Json. Não vai chamar as propriedades de tratamento nem inserir o conéudo na tag **__alert**
 