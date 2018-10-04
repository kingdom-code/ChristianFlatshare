<?php

/* areaDrilldown.html.twig */
class __TwigTemplate_fe967e33491b1c38cade67183c413710 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<h3>";
        echo twig_escape_filter($this->env, twig_capitalize_string_filter($this->env, (isset($context["kind"]) ? $context["kind"] : null)), "html", null, true);
        echo " Accommodation<span>";
        if ((!twig_test_empty((isset($context["back"]) ? $context["back"] : null)))) {
            echo " - <a href=\"";
            echo twig_escape_filter($this->env, (isset($context["back"]) ? $context["back"] : null), "html", null, true);
            echo "\">Back</a> | <a href=\"";
            echo twig_escape_filter($this->env, (isset($context["all"]) ? $context["all"] : null), "html", null, true);
            echo "\" class=\"external\">Show All</a> ";
        }
        echo "</span></h3>
";
        // line 2
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["columns"]) ? $context["columns"] : null));
        foreach ($context['_seq'] as $context["column"] => $context["links"]) {
            // line 3
            echo "    ";
            if ((!twig_test_empty((isset($context["links"]) ? $context["links"] : null)))) {
                // line 4
                echo "        <ul class=\"column-";
                echo twig_escape_filter($this->env, (isset($context["column"]) ? $context["column"] : null), "html", null, true);
                echo "\">
            ";
                // line 5
                $context['_parent'] = (array) $context;
                $context['_seq'] = twig_ensure_traversable((isset($context["links"]) ? $context["links"] : null));
                foreach ($context['_seq'] as $context["_key"] => $context["link"]) {
                    // line 6
                    echo "                <li><a href=\"";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["link"]) ? $context["link"] : null), "url"), "html", null, true);
                    echo "\" class=\"";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["link"]) ? $context["link"] : null), "classes"), "html", null, true);
                    echo "\">";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["link"]) ? $context["link"] : null), "title"), "html", null, true);
                    echo "</a> (";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["link"]) ? $context["link"] : null), "num_ads"), "html", null, true);
                    echo ")</li>
            ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['link'], $context['_parent'], $context['loop']);
                $context = array_merge($_parent, array_intersect_key($context, $_parent));
                // line 8
                echo "        </ul>
    ";
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['column'], $context['links'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 11
        if (twig_test_empty($this->getAttribute((isset($context["columns"]) ? $context["columns"] : null), 0))) {
            // line 12
            echo "    <p>No accommodation currently available.</p>
";
        }
    }

    public function getTemplateName()
    {
        return "areaDrilldown.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  73 => 12,  71 => 11,  63 => 8,  48 => 6,  44 => 5,  39 => 4,  36 => 3,  32 => 2,  19 => 1,);
    }
}
