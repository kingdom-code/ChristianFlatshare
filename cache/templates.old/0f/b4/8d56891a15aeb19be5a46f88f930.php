<?php

/* mutualFriends.html.twig */
class __TwigTemplate_0fb48d56891a15aeb19be5a46f88f930 extends Twig_Template
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
        if ((!twig_test_empty((isset($context["friends"]) ? $context["friends"] : null)))) {
            // line 2
            echo "<div class=\"FBFriends\">
    <div class=\"label\">Mutual Friends</div>
";
            // line 4
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["friends"]) ? $context["friends"] : null));
            foreach ($context['_seq'] as $context["_key"] => $context["friend"]) {
                // line 5
                echo "<a href=\"http://www.facebook.com/";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["friend"]) ? $context["friend"] : null), "facebook_id"), "html", null, true);
                echo "\" target=\"_blank\"><img src=\"";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["friend"]) ? $context["friend"] : null), "picture_url"), "html", null, true);
                echo "\" width=\"40\" height=\"40\" alt=\"";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["friend"]) ? $context["friend"] : null), "first_name"), "html", null, true);
                echo " ";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["friend"]) ? $context["friend"] : null), "last_name"), "html", null, true);
                echo "\" title=\"";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["friend"]) ? $context["friend"] : null), "first_name"), "html", null, true);
                echo " ";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["friend"]) ? $context["friend"] : null), "last_name"), "html", null, true);
                echo "\" /></a>
";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['friend'], $context['_parent'], $context['loop']);
            $context = array_merge($_parent, array_intersect_key($context, $_parent));
            // line 7
            echo "</div>";
        }
    }

    public function getTemplateName()
    {
        return "mutualFriends.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  48 => 7,  29 => 5,  25 => 4,  21 => 2,  44 => 15,  40 => 14,  23 => 3,  84 => 46,  80 => 44,  76 => 42,  74 => 41,  63 => 32,  55 => 26,  47 => 20,  45 => 19,  27 => 4,  22 => 2,  19 => 1,);
    }
}
