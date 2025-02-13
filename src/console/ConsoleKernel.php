<?php

namespace xamned\framework\console;

use xamned\framework\console\commands\ListCommand;
use xamned\framework\contracts\console\ConsoleCommandInterface;
use xamned\framework\contracts\console\ConsoleInputInterface;
use xamned\framework\contracts\console\ConsoleKernelInterface;
use xamned\framework\contracts\console\ConsoleOutputInterface;
use xamned\framework\contracts\container\ContainerInterface;
use xamned\framework\contracts\ErrorHandlerInterface;
use xamned\framework\contracts\logger\LoggerInterface;
use \Throwable;

/**
 * Ядро обработки вызова консоли
 */
class ConsoleKernel implements ConsoleKernelInterface
{
    private string $defaultCommand = 'list';

    private array $commandMap = [];

    public function __construct(
    	private readonly ContainerInterface $container,
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly LoggerInterface $logger,
        private readonly ErrorHandlerInterface $errorHandler,
        private readonly string $appName,
        private readonly string $version,
    )
    {
        $this->initDefaultCommands();
    }

    public function getAppName(): string
    {
        return $this->appName;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getCommands(): array
    {
        return $this->commandMap;
    }

    public function registerCommandNamespaces(array $commandNameSpaces): void
    {
    	foreach ($commandNameSpaces as $commandNameSpace) {
            $this->registerCommandNamespace($commandNameSpace);
        }
    }

    /**
     * Регистрация класса команды
     * 
     * @return void
     */
    private function registerCommand(string $className): void
    {
        if (is_subclass_of($className, ConsoleCommandInterface::class) === false) {
            throw new \Error("$className не соответствует интерфейсу - " . ConsoleCommandInterface::class);
        }
        
        $commandName = (new CommandDefinition($className::getSignature(), $className::getDescription()))->getCommandName();

        $this->commandMap[$commandName] = $className;
    }

    /**
     * Регистрация неймспейса команды
     * 
     * @return void
     */
    private function registerCommandNamespace(string $commandNameSpace): void
    {
        $paths = array_diff(scandir($commandNameSpace), ['..', '.']);

        foreach ($paths as $path) {
            $fullPath = $commandNameSpace . DIRECTORY_SEPARATOR . $path;

            if (is_dir($fullPath) === true) {
                $this->registerCommandNamespace($fullPath);
            }

            if (is_file($fullPath) === false) {
                continue;
            }

            $matches = [];

            preg_match("/^[A-Z].+(?=\.php)/", $path, $matches);

            if ($matches === []) {
                continue;
            }

            $commandClass = str_replace(PROJECT_ROOT, '', $commandNameSpace) . DIRECTORY_SEPARATOR .   $matches[0];

            $commandClass = str_replace('/', '\\', $commandClass);
            
            $this->registerCommand($commandClass);
        }
    }
    
    /**
     * Регистрация команд по-умолчанию
     * 
     * @return void
     */
    private function initDefaultCommands(): void
    {
        $defaultCommands = [
            ListCommand::class,
        ];

        foreach ($defaultCommands as $className) {
            $this->registerCommand($className);
        }
    }

    public function handle(): int
    {
        try {
            $commandName = $this->input->getFirstArgument() ?? $this->defaultCommand;
    
            $commandName = $this->commandMap[$commandName]
                ?? throw new \InvalidArgumentException(sprintf("Команда %s не найдена", $commandName));
    		
    	    $this->container
            	->build($commandName)
            	->execute();
            	
        } catch (Throwable $e) {
            $message = $this->errorHandler->handle($e);

            $this->output->stdErr($message);

            $this->logger->error([
                'category' => $this::class,
                'message' => $e->getMessage(),
            ]);

            return 1;
        }

        return 0;
    }

    public function terminate(int $status): never
    {
        exit($status);
    }
}
