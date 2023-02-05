<?php

namespace Nuwave\Lighthouse\SoftDeletes;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\ModifyModelExistenceDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;

class ForceDeleteDirective extends ModifyModelExistenceDirective implements FieldManipulator
{
    public const MODEL_NOT_USING_SOFT_DELETES = 'Use the @forceDelete directive only for Model classes that use the SoftDeletes trait.';

    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
"""
Permanently remove one or more soft deleted models.
"""
directive @forceDelete(
  """
  Specify the class name of the model to use.
  This is only needed when the default model detection does not work.
  """
  model: String
) on FIELD_DEFINITION
GRAPHQL;
    }

    protected function enhanceBuilder(EloquentBuilder $builder): EloquentBuilder
    {
        /** @see \Illuminate\Database\Eloquent\SoftDeletes */
        // @phpstan-ignore-next-line because it involves mixins
        return $builder->withTrashed();
    }

    protected function modifyExistence(Model $model): bool
    {
        /** @see \Illuminate\Database\Eloquent\SoftDeletes */
        // @phpstan-ignore-next-line because it involves mixins
        return (bool) $model->forceDelete();
    }

    public function manipulateFieldDefinition(
        DocumentAST &$documentAST,
        FieldDefinitionNode &$fieldDefinition,
        ObjectTypeDefinitionNode &$parentType
    ): void {
        SoftDeletesServiceProvider::assertModelUsesSoftDeletes(
            $this->getModelClass(),
            self::MODEL_NOT_USING_SOFT_DELETES
        );
    }
}
