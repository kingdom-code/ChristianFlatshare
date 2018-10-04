<?php

/* churchDirectoryList.twig */
class __TwigTemplate_ef2908ac12b5d5458b214ecd4268e09b extends Twig_Template
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
        echo "<div class=\"regionSelector\">
    ";
        // line 2
        if ((!twig_test_empty((isset($context["regions"]) ? $context["regions"] : null)))) {
            // line 3
            echo "    Choose a region:
    <select>
        ";
            // line 5
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["regions"]) ? $context["regions"] : null));
            foreach ($context['_seq'] as $context["_key"] => $context["region"]) {
                // line 6
                echo "            ";
                if (($this->getAttribute((isset($context["region"]) ? $context["region"] : null), "name") == (isset($context["current_region"]) ? $context["current_region"] : null))) {
                    // line 7
                    echo "                <option value=\"";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["region"]) ? $context["region"] : null), "name"), "html", null, true);
                    echo "\" selected=\"selected\">";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["region"]) ? $context["region"] : null), "name"), "html", null, true);
                    echo " (";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["region"]) ? $context["region"] : null), "churches"), "html", null, true);
                    echo ")</option>
            ";
                } else {
                    // line 9
                    echo "                <option value=\"";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["region"]) ? $context["region"] : null), "name"), "html", null, true);
                    echo "\">";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["region"]) ? $context["region"] : null), "name"), "html", null, true);
                    echo " (";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["region"]) ? $context["region"] : null), "churches"), "html", null, true);
                    echo ")</option>
            ";
                }
                // line 11
                echo "        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['region'], $context['_parent'], $context['loop']);
            $context = array_merge($_parent, array_intersect_key($context, $_parent));
            // line 12
            echo "    </select>
    ";
        } else {
            // line 14
            echo "    <p>There are currently no churches or organisations supporting CFS in this country</p>
    ";
        }
        // line 16
        echo "</div>
<div class=\"churchDirectory\">
<ul class=\"left\">
    ";
        // line 19
        $context["i"] = 1;
        // line 20
        echo "    ";
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["churches"]) ? $context["churches"] : null));
        $context['loop'] = array(
          'parent' => $context['_parent'],
          'index0' => 0,
          'index'  => 1,
          'first'  => true,
        );
        if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof Countable)) {
            $length = count($context['_seq']);
            $context['loop']['revindex0'] = $length - 1;
            $context['loop']['revindex'] = $length;
            $context['loop']['length'] = $length;
            $context['loop']['last'] = 1 === $length;
        }
        foreach ($context['_seq'] as $context["_key"] => $context["church"]) {
            // line 21
            echo "        ";
            if ((twig_number_format_filter($this->env, (twig_length_filter($this->env, (isset($context["churches"]) ? $context["churches"] : null)) / 2), 0) == ($this->getAttribute((isset($context["loop"]) ? $context["loop"] : null), "index") - 1))) {
                // line 22
                echo "        </ul><ul class=\"right\">
            ";
                // line 23
                $context["i"] = 1;
                // line 24
                echo "        ";
            }
            // line 25
            echo "        <li ";
            if (((isset($context["i"]) ? $context["i"] : null) % 2 == 0)) {
                echo " class=\"even\" ";
            }
            echo ">[<a href=\"#";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["church"]) ? $context["church"] : null), "church_id"), "html", null, true);
            echo "\" class=\"mapPopUp\">MAP</a>] <a href=\"http://";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["church"]) ? $context["church"] : null), "church_url"), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["church"]) ? $context["church"] : null), "church_name"), "html", null, true);
            echo "</a>";
            if ((!twig_test_empty($this->getAttribute((isset($context["church"]) ? $context["church"] : null), "route")))) {
                echo ", ";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["church"]) ? $context["church"] : null), "route"), "html", null, true);
            }
            echo "</li>
        ";
            // line 26
            $context["i"] = ((isset($context["i"]) ? $context["i"] : null) + 1);
            // line 27
            echo "    ";
            ++$context['loop']['index0'];
            ++$context['loop']['index'];
            $context['loop']['first'] = false;
            if (isset($context['loop']['length'])) {
                --$context['loop']['revindex0'];
                --$context['loop']['revindex'];
                $context['loop']['last'] = 0 === $context['loop']['revindex0'];
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['church'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 28
        echo "</ul>
<div class=\"clearfix\"></div>
</div>";
    }

    public function getTemplateName()
    {
        return "churchDirectoryList.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  139 => 28,  125 => 27,  123 => 26,  105 => 25,  102 => 24,  100 => 23,  97 => 22,  94 => 21,  76 => 20,  74 => 19,  69 => 16,  65 => 14,  61 => 12,  55 => 11,  45 => 9,  35 => 7,  32 => 6,  28 => 5,  24 => 3,  22 => 2,  19 => 1,);
    }
}
