<?php

/* loginOrRegister.twig */
class __TwigTemplate_e8eadd2577e200ba9a6bcc9f9319b23a extends Twig_Template
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
</div>
<div><a href=\"register.php\" id=\"post-advert\">Post an Advert</a></div>
<div id=\"loginContainer\" class=\"mb20\">
    <div class=\"box_grey mb10\">
    \t<div class=\"tr\"><span class=\"l\"></span><span class=\"r\"></span></div>
    \t<div class=\"mr\">
    \t    <h2 class=\"m0 login-title\">Member Login</h2>
            ";
        // line 14
        echo (isset($context["loginForm"]) ? $context["loginForm"] : null);
        echo "
            <a href=\"";
        // line 15
        echo twig_escape_filter($this->env, (isset($context["FacebookLoginURL"]) ? $context["FacebookLoginURL"] : null), "html", null, true);
        echo "\" class=\"fb-login\">Login with Facebook</a>
    \t</div>
    \t<div class=\"br\"><span class=\"l\"></span><span class=\"r\"></span></div>
    </div>
    <p class=\"m0\"></p>
</div>
<p class=\"mt0 mb10\" align=\"justify\">Christian Flatshare is <b>free</b> to join and to use.</p>";
    }

    public function getTemplateName()
    {
        return "loginOrRegister.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  44 => 15,  40 => 14,  27 => 4,  23 => 3,  19 => 1,);
    }
}
