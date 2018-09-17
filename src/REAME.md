# NZord -Doc v0.0.1


### Modelo de rotas
 `/app/{modulo}/{pagina}`
 
*  Modulo: Nome pasta de modulo 
*  Pagina: Nome do constroller 

### Template 
``@twMod`` - namespace para acessar templates padrão.


#### ``backend.html.twig``
    - blocks
        - page_title
        - content
        - container
        - scripts 


#### Componentes Twig

- base/grid.twig
    - grid
    - gridSripts(nome, button={}, url, dados)
    - ctMenuSripts


### Funções JS

``loadDetailBoxJs(nomeModal,url);``