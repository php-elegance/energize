# Page

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

Você deve criar o layout na pasta **view/layout**. Um layout é uma view que será utilizada para encapsular o conteúdo
Para alterar o layout de uma resposta, utilize o metodod **Front::layout('layoutName')**

    Front::layout('default');

### Estado de layout

Por padrão, layouts são estaticos e, a não ser que o arquivo mude, se mantem inalterados durante a navegação.
Você pode desativar este comportamento com o metodo metodod **Front::layoutState('groupName')**

    Front::layoutState('state');

Uma requisições que utilizam estados diferentes sempre terão o layout atualizado.

### Conteúdo Aside

Para adicionar conteúdo aside ao frontend, utilize o metodo **Front::aside('asideHtml')**

    Front::aside('asideHtml','asideName');

O conteúdo aside vai ser inserido dentro da tag prepade **[#aside.asideName]**.

### Funcionamento

Sempre que uma resposta passar pela middleware **energize** ela será tratada como uma página de frontend e vai respeitar a seguintes regras.

  - Sempre que uma página for acessada diretamente ela via ser entregue em HTML
  - Sempre que uma página for chamada dinamicamente, ela vai retornar um JSON com o conteúdo
  - Sempre que uma página utiliar um layout diferente do layout atual, ela vai retornar um JSON com o layout e o conteúdo

 > Essa implementação não precisa ser feita. tudo já está aplicado e instalado no arquivo front.js
