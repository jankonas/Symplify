<?php declare(strict_types=1);

namespace Symplify\PackageBuilder\Tests\Yaml\ParameterInImportResolverSource;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symplify\PackageBuilder\Yaml\ParameterInImportResolver;

final class ImportParameterAwareYamlFileLoader extends YamlFileLoader
{
    /**
     * @var ParameterInImportResolver
     */
    private $parameterInImportResolver;

    public function __construct(ContainerBuilder $containerBuilder, FileLocatorInterface $fileLocator)
    {
        $this->parameterInImportResolver = new ParameterInImportResolver();

        parent::__construct($containerBuilder, $fileLocator);
    }

    /**
     * @param string $file
     * @return mixed|mixed[]
     */
    protected function loadFile($file)
    {
        $configuration = parent::loadFile($file);

        return $this->parameterInImportResolver->process($configuration);
    }
}
