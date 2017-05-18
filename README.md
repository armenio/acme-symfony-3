# Gerenciador de produtos Acme Symfony 3

Api para gestão de produtos da empresa Acme

#### Tecnologias utilizadas
- Symfony 3
- Doctrine ORM
- Autenticação com token JWT usando o Bundle LexikJWTAuthenticationBundle: https://github.com/lexik/LexikJWTAuthenticationBundle
- Composer como gerenciador de dependências php
- Banco de dados MySql

## Instalação do projeto

    $ git clone https://github.com/armenio/acme-symfony-3.git aplicacao
    $ cd aplicacao
    $ composer install

Não conhece o composer? [Veja aqui](http://getcomposer.org/doc/00-intro.md#introduction) como usá-lo

É possível usar o composer sem instalação com o comando:

    $ /caminho/do/php /caminho/do/composer.phar install

### O arquivo para criação do banco de dados encontra-se em:
/caminho/da/aplicacao/var/db.sql

### Configuração do acesso ao bando de dados:
A configuração do banco de dados e servidor de e-mail é feita durante a instalação

### Configuração do disparo de emails de relatórios:
Editar as linhas 231 e 232 do arquivo /caminho/do/aplicacao/src/AppBundle/Controller/ProductsController.php

## Rodando a aplicação
     $ cd /caminho/da/aplicacao
     $ php bin/console server:run
