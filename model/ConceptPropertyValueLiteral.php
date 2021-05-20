<?php

/**
 * Class for handling concept property values.
 */
class ConceptPropertyValueLiteral extends VocabularyDataObject
{
    /** the literal object for the property value */
    private $literal;
    /** property type */
    private $type;

    public function __construct($model, $vocab, $resource, $literal, $prop)
    {
        parent::__construct($model, $vocab, $resource);
        $this->literal = $literal;
        $this->type = $prop;
    }

    public function __toString()
    {
        if ($this->getLabel() === null) {
            return "";
        }

        return $this->getLabel();
    }

    public function getLang()
    {
        // *****
        error_log("Entered to the getLang()");
        error_log($this->getDatatype());
        // *****
        return $this->literal->getLang();
    }

// ****
    // This method is only for testing purposes. An idea is just to print needed data ...
    public function getDatatype()
    {
        error_log("Entered to erroneous area.. 1");
        $sparql = new \EasyRdf\Sparql\Client('http://localhost:3030/skosmos/query');
        $result = $sparql->query(
            'SELECT ?o WHERE {'.
            '  <http://www.yso.fi/onto/juho/dt1> rdfs:label ?o .'.
            '}'
        );
        error_log("*********");
        error_log("Yksi kerraallaan....");
        foreach ($result as $row) {
          error_log(var_dump($row->o)); // Käytä tätä kun haluat nähdä käsitesivulla "kaikki"
        }
        error_log("*****************");
        error_log("Yksittäinen");
        error_log($this->literal);
        return $this->literal->getDatatype();
    }
// ****

    public function getType()
    {
        return $this->type;
    }

    public function getContainsHtml() {
        return preg_match("/\/[a-z]*>/i", $this->literal->getValue()) != 0;
    }

    public function getLabel()
    {
        // if the property is a date object converting it to a human readable representation.
        if ($this->literal instanceof EasyRdf\Literal\Date) {
            try {
                $val = $this->literal->getValue();
                return Punic\Calendar::formatDate($val, 'short');
            } catch (Exception $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
                return (string) $this->literal;
            }
        }
        return $this->literal->getValue();
    }

    public function getUri()
    {
        return null;
    }

    public function getNotation()
    {
        return null;
    }

    public function hasXlProperties()
    {
        $graph = $this->resource->getGraph();
        $resources = $graph->resourcesMatching('skosxl:literalForm', $this->literal);
        return !empty($resources);
    }

    public function getXlProperties()
    {
        $ret = array();
        $graph = $this->resource->getGraph();
        $resources = $graph->resourcesMatching('skosxl:literalForm', $this->literal);
        foreach ($resources as $xlres) {
            foreach ($xlres->properties() as $prop) {
                foreach($graph->allLiterals($xlres, $prop) as $val) {
                    if ($prop !== 'rdf:type') {
                        $ret[$prop] = $val;
                    }
                }
            }
        }
        return $ret;
    }

}
