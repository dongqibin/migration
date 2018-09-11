####自己的数据库迁移类  
####依赖于tp自带的数据库迁移.
####配置方法
#####在application/command.php数组内添加 'migration\command\DjCreate' 元素
在项目根目录下执行命令 php think migrate:DjCreate 表名(驼峰)

