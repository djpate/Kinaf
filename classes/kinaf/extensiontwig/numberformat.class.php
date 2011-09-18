<?php

namespace kinaf\extensiontwig;

class numberFormat extends \Twig_Extension
{
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'numberFormat';
    }

    public function getFunctions()
    {
        return array(
            'numberFormat' => new \Twig_Function_Method($this, 'numberFormat'),
        );
    }

    public function numberFormat($number, $decimals = 2, $dec_point = ',', $thousands_sep = ' ') {
    	return number_format(round($number, $decimals), $decimals, $dec_point, $thousands_sep);
    }
}

?>
