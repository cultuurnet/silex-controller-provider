<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 26/10/15
 * Time: 16:17
 */

namespace CultuurNet\UDB3SilexEntryAPI;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\UnequalAmountOfValuesException;
use ValueObjects\String\String;

class KeywordsVisiblesPair
{
    /**
     * @var array
     */
    protected $keywords;

    /**
     * @var array
     */
    protected $visibles;

    /**
     * @return LabelCollection
     */
    public function getLabels()
    {
        return $this->createLabels($this->keywords, $this->visibles);
    }

    /**
     * @return array
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @return array
     */
    public function getVisibles()
    {
        return $this->visibles;
    }

    /**
     * @param String $keywords
     * @param String $visibles
     */
    public function __construct(String $keywords, String $visibles)
    {
        $this->keywords = $keywords;
        $this->visibles = $visibles;

        $this->keywords = explode(';', $keywords->toNative());
        $this->visibles = $this->getVisiblesAsArray($visibles);

        // If no visibility is provided, default to visible (true).
        if (empty($this->visibles)) {
            $this->visibles = array_fill(0, count($this->keywords), 'true');
        }

        if (count($this->keywords) != count($this->visibles)) {
            throw new UnequalAmountOfValuesException('keywords', 'visibles');
        }

        // @todo Ensure visibles only contains true or false.
    }

    /**
     * @param String $visibles
     */
    private function getVisiblesAsArray(String $visibles)
    {
        if ('' === trim($visibles->toNative())) {
            return [];
        }

        return explode(';', $visibles->toNative());
    }

    /**
     * @param array $keywords
     * @param array $visibles
     * @return LabelCollection
     */
    private function createLabels(array $keywords, array $visibles)
    {
        $labels = array();

        foreach ($keywords as $key => $keyword) {
            $visible = $visibles[$key] === 'true' ? true : false;
            $labels[] = new Label(trim($keyword), $visible);
        }

        $collection = new LabelCollection();
        foreach ($labels as $label) {
            if (!$collection->contains($label)) {
                $collection = $collection->with($label);
            }
        }

        return $collection;
    }
}
