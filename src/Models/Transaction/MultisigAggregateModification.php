<?php
/**
 * Part of the evias/nem-php package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under MIT License.
 *
 * This source file is subject to the MIT License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    evias/nem-php
 * @version    1.0.0
 * @author     Grégory Saive <greg@evias.be>
 * @author     Robin Pedersen (https://github.com/RobertoSnap)
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Models\Transaction;

use NEM\Models\Transaction;

class MultisigAggregateModification
    extends Transaction
{
    /**
     * List of additional fillable attributes
     *
     * @var array
     */
    protected $appends = [
        "modifications",
        "minCosignatories",
        "relativeChange",
    ];

    /**
     * The Signature transaction type does not need to add an offset to
     * the transaction base DTO.
     *
     * @return array
     */
    public function extend() 
    {
        return [
            "modifications" => $this->modifications()->toDTO(),
            "minCosignatories" => [
                "relativeChange" => $this->relativeChange
            ],
        ];
    }

    /**
     * Mutator for the modifications collection.
     *
     * @return \NEM\Models\ModelCollection
     */
    public function modifications(array $modifications = null)
    {
        $mods = $modifications ?: $this->getAttribute("modifications") ?: [];
        return (new CollectionMutator())->mutate("multisigModification", $mods);
    }
}
