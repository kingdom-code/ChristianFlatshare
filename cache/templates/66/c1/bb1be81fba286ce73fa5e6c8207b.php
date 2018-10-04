<?php

/* base.html.twig */
class __TwigTemplate_66c1bb1be81fba286ce73fa5e6c8207b extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'stylesheets' => array($this, 'block_stylesheets'),
            'header' => array($this, 'block_header'),
            'body' => array($this, 'block_body'),
            'footer' => array($this, 'block_footer'),
            'scripts' => array($this, 'block_scripts'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"utf-8\">
        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">
        <title>";
        // line 6
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
        
        <link rel=\"icon\" sizes=\"16x16\" href=\"favicon.ico\" />
        
        ";
        // line 10
        $this->displayBlock('stylesheets', $context, $blocks);
        // line 13
        echo "    </head>
    <body>
        ";
        // line 15
        $this->displayBlock('header', $context, $blocks);
        // line 16
        echo "        ";
        $this->displayBlock('body', $context, $blocks);
        // line 17
        echo "        ";
        $this->displayBlock('footer', $context, $blocks);
        // line 18
        echo "        ";
        $this->displayBlock('scripts', $context, $blocks);
        // line 21
        echo "    </body>
</html>";
    }

    // line 6
    public function block_title($context, array $blocks = array())
    {
        echo "Christian Flatshare";
    }

    // line 10
    public function block_stylesheets($context, array $blocks = array())
    {
        // line 11
        echo "            <link rel=\"stylesheet\" href=\"css/main.css\" />
        ";
    }

    // line 15
    public function block_header($context, array $blocks = array())
    {
    }

    // line 16
    public function block_body($context, array $blocks = array())
    {
    }

    // line 17
    public function block_footer($context, array $blocks = array())
    {
    }

    // line 18
    public function block_scripts($context, array $blocks = array())
    {
        // line 19
        echo "            <script src=\"js/main-ck.js\"></script>
        ";
    }

    public function getTemplateName()
    {
        return "base.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  93 => 19,  90 => 18,  85 => 17,  80 => 16,  75 => 15,  70 => 11,  67 => 10,  61 => 6,  56 => 21,  53 => 18,  50 => 17,  47 => 16,  45 => 15,  41 => 13,  39 => 10,  32 => 6,  25 => 1,  35 => 5,  29 => 3,);
    }
}
