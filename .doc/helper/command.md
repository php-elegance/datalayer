# Command

**dbdriver**: Cria os drivers para uma conexão datalayer

    php mx dbdriver nomeDaConexão

**migration.create**: Cria um arquivo de migration em **source/migration**

    php mx migration.create nomeDaConexao

**migration.down**: Reverte a ultima migration

    php mx migration.down nomeDaConexao

**migration.run**: Executa todas as migrations pendentes

    php mx migration.run nomeDaConexao

**migration.up**: Executa a proxima migation

    php mx migration.up nomeDaConexao
