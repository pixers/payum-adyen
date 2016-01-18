<?php
namespace Payum\Adyen\Bridge\Symfony;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AbstractGatewayFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class AdyenGatewayFactory extends AbstractGatewayFactory
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'adyen';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);

        $builder->children()
            ->scalarNode('skinCode')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('merchantAccount')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('hmacKey')->isRequired()->cannotBeEmpty()->end()
            ->booleanNode('sandbox')->defaultTrue()->end()
            ->end();
    }

    /**
     * {@inheritDoc}
     */
    protected function getPayumGatewayFactoryClass()
    {
        return 'Payum\Adyen\AdyenGatewayFactory';
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackage()
    {
        return 'NetTeam/payum-adyen';
    }
}
