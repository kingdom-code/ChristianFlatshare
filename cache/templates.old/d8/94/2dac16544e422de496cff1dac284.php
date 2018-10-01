<?php

/* emails/report.html.twig */
class __TwigTemplate_d8942dac16544e422de496cff1dac284 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = $this->env->loadTemplate("emails/base.html.twig");

        $this->blocks = array(
            'body' => array($this, 'block_body'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "emails/base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_body($context, array $blocks = array())
    {
        // line 4
        echo "    <p>A visitor has reported the following: <strong><a href=\"";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["advert"]) ? $context["advert"] : null), "url"), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["advert"]) ? $context["advert"] : null), "title"), "html", null, true);
        echo "</a></strong></p>
    <p>Visitor details:</p>
    <p>Name: <strong>";
        // line 6
        echo twig_escape_filter($this->env, (isset($context["name"]) ? $context["name"] : null), "html", null, true);
        echo "</strong><br/>
    Email: <strong>";
        // line 7
        echo twig_escape_filter($this->env, (isset($context["email"]) ? $context["email"] : null), "html", null, true);
        echo "</strong></p>
    <p>Their message:</p>
    <p><strong>";
        // line 9
        echo nl2br(twig_escape_filter($this->env, (isset($context["message"]) ? $context["message"] : null), "html", null, true));
        echo "</strong></p>
";
    }

    public function getTemplateName()
    {
        return "emails/report.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  48 => 9,  43 => 7,  39 => 6,  31 => 4,  28 => 3,);
    }
}
