<?php
/**
 * 自己的数据迁移创建类
 * Created by PhpStorm.
 * User: DJ
 * Date: 2018/9/6
 * Time: 18:31
 */

namespace migration\command;

use Phinx\Util\Util;
use think\console\input\Argument as InputArgument;
use think\console\Input;
use think\console\Output;

class DjCreate extends \think\migration\command\migrate\Create
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migrate:djCreate')
             ->setDescription('Create a new migration')
             ->addArgument('name', InputArgument::REQUIRED, 'What is the name of the migration?')
             ->setHelp(sprintf('%sCreates a new database migration%s', PHP_EOL, PHP_EOL));
    }

    /**
     * Create the new migration.
     *
     * @param Input  $input
     * @param Output $output
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function execute(Input $input, Output $output)
    {
        $path = $this->getPath();

        if (!file_exists($path)) {
            if ($this->output->confirm($this->input, 'Create migrations directory? [y]/n')) {
                mkdir($path, 0755, true);
            }
        }

        $this->verifyMigrationDirectory($path);

        $path      = realpath($path);
        $className = $input->getArgument('name');

        if (!Util::isValidPhinxClassName($className)) {
            throw new \InvalidArgumentException(sprintf('The migration class name "%s" is invalid. Please use CamelCase format.', $className));
        }

        if (!Util::isUniqueMigrationClassName($className, $path)) {
            throw new \InvalidArgumentException(sprintf('The migration class name "%s" already exists', $className));
        }

        // Compute the file path
        $fileName = Util::mapClassNameToFileName($className);
        $filePath = $path . DS . $fileName;

        if (is_file($filePath)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" already exists', $filePath));
        }

        // Verify that the template creation class (or the aliased class) exists and that it implements the required interface.
        $aliasedClassName = null;

        // Load the alternative template if it is defined.
        $contents = file_get_contents($this->getTemplate());

        // inject the class names appropriate to this migration
        $tableName = $this->_getTableName($className);
        $contents = strtr($contents, [
            '$className' => $className,
            '$tableName' => $tableName,
        ]);

        if (false === file_put_contents($filePath, $contents)) {
            throw new \RuntimeException(sprintf('The file "%s" could not be written to', $path));
        }

        $output->writeln('<info>created</info> .' . str_replace(getcwd(), '', $filePath));
    }

    protected function getTemplate()
    {
        return __DIR__ . '/../stubs/migrate.stub';
    }

    private function _getTableName($className) {
        $arr = preg_split('/(?=[A-Z])/', $className);
        unset($arr[0]); // remove the first element ('')
        $tableName = strtolower(implode($arr, '_'));
        return $tableName;
    }

}
