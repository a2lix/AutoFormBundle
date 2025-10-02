<?php

declare(strict_types=1);

/*
 * This file is part of the AutoFormBundle package.
 *
 * (c) David ALLIX <http://a2lix.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use A2lix\AutoFormBundle\Form\Builder\AutoTypeBuilder;
use A2lix\AutoFormBundle\Form\Type\AutoType;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('a2lix_auto_form.form.builder.auto_type_builder', AutoTypeBuilder::class)
        ->args([
            service('property_info'),
        ])
        ->set('a2lix_auto_form.form.type.auto_type', AutoType::class)
        ->args([
            service('a2lix_auto_form.form.builder.auto_type_builder'),
            null, // children_excluded config option
        ])
        ->tag('form.type')
    ;
};
