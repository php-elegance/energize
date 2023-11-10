# Energize.js

Biblioteca javascript para dinamismo em projetos Elegance

O arquivo javascript pode ser encontrado em **library/assets/script/energize.js**

### Inicilização

Utilize o comando para fazer a instalação do front

    php front install.energize

Este comando vai instalar todos os arquivos nescessarios. 

### Funcionamento

O front funciona como uma ponte entre o frontend e o backend de seu projeto. É ativado automaticamente sempre a reposta tiver a middleware **energize**.

### Load Dinamico

Esta função impede que a página seja recarregada sem motivo, o front detecta a base, estrutura e conteúdo utilizado e modifica apenas o que for nescessario.

Por padrão, a o **energize** busca todos os elementos com a propredade **href**, apontando para o mesmo dominio, e aplica o lod dinamico. Você pode impedir este comportamento injetando a propriedade **energized** em uma tag.

    <a href='meuprojeto'>meu link</a> // Este link vai ser energizada
    <a href='meuprojeto' energized>meu link</a> // Esta tag não vai ser energizada
    <a href='outroprojeto'>meu link</a> // Esta tag não vai ser energizada

Para utilizar o load dinamico dentro de um aplicativo VueJs, utilize o metodo exposto **front.go**

    <script>
        export default :{
            methods: {
                link() {
                    energize.go("url");
                },
            },
        }
    </script>

### __page

Devido a forma como o javascript funciona, não é possivel utilizar funções globais em chamadas dinamicas. Se precisar criar metodos ou objetos, adicione-os ao objeto **__page**

    function myFunction(){...} //comportamento inprevisivel
    __page.myFunction = (){...} //funciona como esperado

### Submit automático

Esta função adiciona uma forma simplificada de realizar submits internos em seu projeto.
Para evitar que um formuário seja submetido via **energize**, adicione a propriedade **energized**.

    <form></form> // Formulário submetido via energize
    <form energized></form> // Formulário submetido normalmente

O formulário será automaticamente submetido utilizando as propriedes **method** e **action** da tag. O valor padrão para **action** é a URL atual. O valor padrão para **method** é **post**.

**Tratamento de respostas**
Para definir um tratamento de sucesso personalizado, adicione o nome da função na propriedade **data-success**
Para definir um tratamento de erro personalizado, adicione o nome da função na propriedade **data-errro**
O **energize** considera sucesso qualquer valor de retorno com o status >= 300

    <form data-success="__page.form_success" data-error="__page.form_error"></form>
    <script>
        __page.form_success = (response) {
            console.log("success", response);
        }
        __page.form_error = (response) {
            console.log("error", response);
        }
    </script>

**Auto alert**
Se o formulário não informar a forma de tratamento da resposta e tiver uma tag com a classe **__alert**, a resposta será exibida dentro desta tag.

    <form front>
        <span class='__alert'></span>
    </form>

A resposta será inserida no seguingue padrão

    <span class='${spanClass}'>
        ${resp.info.message}
    </span>
 