# NZord

Projeto desenvolvido em Slim Framework 3 incluindo Eloquent, Twig, Flash messages, Monolog,... 

##### Instalação 

Faça o clone do projeto

```bash
$ git clone http://192.168.173.49/slim/nzord.git
```

É recomendado que você use o [Composer](https://getcomposer.org/) para a instalação das dependencias.

```bash
$ composer install
```

Altere as configurações de conexões do banco de dados no arquivo `base/settings.php`


#####  Atenção

Definir a permissão de gravação para as pastas `base/tmp` e` files` ao implantar no ambiente de produção.

#####  Gerar Doc
Utilize essa comando para gerar a Documentação do sistema

```bash
$ ./phpDoc.sh
```

##### Diretórios
* `base`: Código de aplicação
* `files`: Diretório gravável dos arquivos jpg, png, pdf, bmp...
* `modulos`: Todos os Modulos utilizados pelo sistema
* `public`: Raiz do webserver
* `vendor`: Composer dependencias


##### Twig
- Funções  Ex: `{{ dataExtenso(datas.data) }}`
    * `dataExtenso()` - Transforma data em data escrita.
    ```twig
        {{ dataExtenso('1969-12-31') }}
        //Result: 31 de dezembro de 1969
    ```
    * `valorPorExtenso()` - Transforma valor em valor escrito.
    ```twig
        {{ valorPorExtenso(52.00) }}
        //Result:  cinquenta e dois reais 
    ```
    * Gera link para modulo  `path_for_model()` -
    ```twig
        {{ path_for_model('meu-modulo','meu-controller','index', [12],['filtro'=>1]) }}  - Gera link
        //Result: http://localhost/nzord/app/meu-modulo/meu-controller/index/12&filtro=1
    ```
    *  Gera link Modal `path_for_modal()` - 
        ```twig
            {{ path_for_modal('meu-modulo','meu-controller','index', [12],['filtro'=>1]) }}  - Gera link para modal
           //Result: http://localhost/nzord/modal?p=app/meu-modulo/meu-controller/index/12&filtro=1
        ```

- Filtros Ex: `{{datas.valor | number_format}}`
    * `number_format` - Formata número para fomato pt-br.
    * `cpfCnpj` - Aplica mascará cpf ou cnpj.
    * `date('Y-m-d')` - Aplica data formato.


##### Modal
    


##### Test unitários e integração.
*  Diretorios para arquivos de testes.
    -   `base\tests\unit`: Pasta teste unitários
        - `meuTestTest.php`
    -   `base\tests\integration`: Pasta teste de integração

* Diretorios para modulos , segue mesmo modelo da base.
    - `modulos\nomemodulo\tests\unit`: Pasta teste unitários
       
    - `modulos\nomemodulo\integration`: Pasta teste de integração

* Executar testes
```bash
$ composer test
```

##### Configura para login via Active Directory (AD) 
- Adicionar configuração. 
    
    Arquivo:  ```base\settings.php```
~~~ php
'auth' => [
    'useAD' => true
]
~~~

- Configurar paramentros. podendo colocar varios servidores para verificação.
    - userAccesAD (JSON)
    ~~~ json
        [
            {"server":"192.168.1.2","user":"admin1","domain":"AD1","pass":"senhaAD1"},
            {"server":"192.168.1.3","user":"admin1","domain":"AD2","pass":"senhaAD2"},
            {"server":"192.168.1.4","user":"admin1","domain":"AD2","pass":"senhaAD3"}
        ]
    ~~~