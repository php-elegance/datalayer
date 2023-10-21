# Helpers

## Command

**migration**: Cria um arquivo de migration para uma conexão datalayer em **source/migration/[dbName]**

    php mx migration [dbName].[migrationName]

**migration.clean**: Desfaz todas as migrations de um datalayer

    php mx migration.clean [dbName]

**migration.down**: Desfaz uma migration de um datalayer

    php mx migration.down [dbName]

**migration.run**: Roda todas as migrations de um datalayer

    php mx migration.run [dbName]

**migration.up**: Roda uma migration de um datalayer

    php mx migration.up [dbName]

**db.driver**: Cria os arquivos de driver para utilização de um datalayer

    php mx db.driver [dbName]

**db.export**: Exporta o conteúdo do banco de dados para um json

    php mx db.export [dbName] [file]

**db.import**: Importa um json para o banco de dados

    php mx db.import [dbName] [file] [useId]

**db.map**: Exporta o mapa do banco de dados para um arquivo json

    php mx dbmap [dbName] [file]

## Config

**DB_[NAME]_TYPE**:Inicialização de um datalayer NAME

    DB_MAIN_TYPE= sqlite 
    DB_MAIN_TYPE= mysql

**DB_[NAME]_FILE**:Arquivo de dados de um datalayer SQLITE

    DB_MAIN_FILE = nameFile

**DB_[NAME]_HOST**: Host para um datalayer MYSQL

    DB_MAIN_HOST = localhost

**DB_[NAME]_DATA**: Nome da base de dados para um datalayer MYSQL

    DB_MAIN_DATA = nameData

**DB_[NAME]_USER**: Usuário para um datalayer MYSQL

    DB_MAIN_USER = root

**DB_[NAME]_PASS**: Senha para um datalayer MYSQL

    DB_MAIN_PASS = 1234
