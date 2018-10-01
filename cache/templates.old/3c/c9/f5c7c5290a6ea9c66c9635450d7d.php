<?php

/* memberMenu.twig */
class __TwigTemplate_3cc9f5c7c5290a6ea9c66c9635450d7d extends Twig_Template
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
        echo "<div class=\"mb20\">
    <div id=\"change-country\">
        <a href=\"countries.php\" class=\"flag\"><img src=\"/images/mid-flags/";
        // line 3
        echo twig_escape_filter($this->env, twig_upper_filter($this->env, $this->getAttribute((isset($context["country"]) ? $context["country"] : null), "iso")), "html", null, true);
        echo ".png\" class=\"mid-flag\" /></a>
        <p class=\"country\"><strong>";
        // line 4
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["country"]) ? $context["country"] : null), "name"), "html", null, true);
        echo "</strong></p>
        <p><a href=\"countries.php\">Change</a></p>
    </div>
</div>";
        // line 8
        if (($this->getAttribute((isset($context["currentUser"]) ? $context["currentUser"] : null), "facebookEnabled") != true)) {
            echo "<a href=\"";
            echo twig_escape_filter($this->env, (isset($context["FacebookLoginURL"]) ? $context["FacebookLoginURL"] : null), "html", null, true);
            echo "\" class=\"fb-login\">Enhance with Facebook</a>";
        }
        // line 9
        if ($this->getAttribute((isset($context["currentUser"]) ? $context["currentUser"] : null), "facebookEnabled")) {
            // line 10
            echo "<div id=\"sideMenuFacebook\">
<img src=\"https://graph.facebook.com/";
            // line 11
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["currentUser"]) ? $context["currentUser"] : null), "facebook_id"), "html", null, true);
            echo "/picture?width=30&amp;height=30\" width=\"30\" height=\"30\" />
<h3>";
            // line 12
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["currentUser"]) ? $context["currentUser"] : null), "name"), "html", null, true);
            echo "</h3>
</div>
<div id=\"sideMenu\" class=\"withFacebook\">";
        } else {
            // line 16
            echo "<div id=\"sideMenu\">
    <h3>";
            // line 17
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["currentUser"]) ? $context["currentUser"] : null), "name"), "html", null, true);
            echo "</h3>";
        }
        // line 20
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["menu"]) ? $context["menu"] : null));
        foreach ($context['_seq'] as $context["title"] => $context["links"]) {
            // line 21
            echo "    
    <h4>";
            // line 22
            echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : null), "html", null, true);
            echo "</h4>
    <ul>
        ";
            // line 24
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["links"]) ? $context["links"] : null));
            foreach ($context['_seq'] as $context["url"] => $context["parts"]) {
                // line 25
                echo "            ";
                if (((isset($context["url"]) ? $context["url"] : null) == (isset($context["currentPage"]) ? $context["currentPage"] : null))) {
                    // line 26
                    echo "                <li class=\"active\"><a href=\"";
                    echo twig_escape_filter($this->env, (isset($context["url"]) ? $context["url"] : null), "html", null, true);
                    echo "\">";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["parts"]) ? $context["parts"] : null), "title"), "html", null, true);
                    if ($this->getAttribute((isset($context["parts"]) ? $context["parts"] : null), "num")) {
                        echo " <span>(";
                        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["parts"]) ? $context["parts"] : null), "num"), "html", null, true);
                        echo ")</span>";
                    }
                    echo "</a></li>
            ";
                } else {
                    // line 28
                    echo "                <li><a href=\"";
                    echo twig_escape_filter($this->env, (isset($context["url"]) ? $context["url"] : null), "html", null, true);
                    echo "\">";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["parts"]) ? $context["parts"] : null), "title"), "html", null, true);
                    if ($this->getAttribute((isset($context["parts"]) ? $context["parts"] : null), "num")) {
                        echo " <span>(";
                        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["parts"]) ? $context["parts"] : null), "num"), "html", null, true);
                        echo ")</span>";
                    }
                    echo "</a></li>
            ";
                }
                // line 30
                echo "        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['url'], $context['parts'], $context['_parent'], $context['loop']);
            $context = array_merge($_parent, array_intersect_key($context, $_parent));
            // line 31
            echo "    </ul>
    
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['title'], $context['links'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 34
        echo "    
    <small>";
        // line 36
        if (((isset($context["showHidden"]) ? $context["showHidden"] : null) == 1)) {
            // line 37
            echo "Searches will <a href=\"?show_hidden_ads=no\">show</a> your hidden ads";
        } else {
            // line 39
            echo "Searches will <a href=\"?show_hidden_ads=yes\">hide</a> your hidden ads";
        }
        // line 41
        echo "</small>
</div>";
        // line 43
        if ((isset($context["refreshFBFriends"]) ? $context["refreshFBFriends"] : null)) {
            // line 45
            echo "<script>
jQuery(document).ready(function(\$) {
  jQuery.ajax('/fb-import.php');
});
</script>";
        }
    }

    public function getTemplateName()
    {
        return "memberMenu.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  136 => 45,  134 => 43,  131 => 41,  128 => 39,  125 => 37,  123 => 36,  120 => 34,  112 => 31,  106 => 30,  93 => 28,  80 => 26,  77 => 25,  73 => 24,  68 => 22,  65 => 21,  61 => 20,  57 => 17,  54 => 16,  48 => 12,  44 => 11,  41 => 10,  39 => 9,  33 => 8,  27 => 4,  23 => 3,  19 => 1,);
    }
}
