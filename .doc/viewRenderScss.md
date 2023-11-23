# ViewRenderScss

Esta é uma extensão da classe [view](https://github.com/php-elegance/server/blob/main/.doc/view.md) para suporte a vuejs

### Considerações view SCSS

As views SCSS terão sem conteúdo minificado antes do retono. Como seu conteúdo é importado via PHP, a chamada include do css não deve ser utilizada. Ao invez disso, utilize a chamada de views para obter o mesmo resultado.

import './newFile.scss'// Vai gerar um Erro 500
[#view:newFile.scss]// Obtem o resultado do import
import url(...)// Pode ser usado normalmente
A classe View vai ignorar estilos ideinticos a outros estilos já inseridos na requisição. Este comportameto acontece apenas em importações de arquivos, não afeta a estilização via tag style

### Compilando SCSS

Por padrão, a instalação do front adiciona uma rota para compilação de views retornadas pela classe [assets](https://github.com/php-elegance/server/blob/main/.doc/assets.md).
A classe [front](https://github.com/php-elegance/energize/blob/main/.doc/front.md) tambem compila o scss de forma automática.
Se precisar compilar texto SCSS em CSS, utilize a classe **Scss**

    Scss::compile($style): string

### Arquivo global SCSS

Para que o compilador utilize um arquivo global no SCSS, utilize o metodo **useInCompile**

    Scss::useInCompile('view',[prepare]);

Este arquivo vai ser importado antes da compilação do SCSS
