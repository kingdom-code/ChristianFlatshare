<?php

/* emails/story.html.twig */
class __TwigTemplate_41fd02eb5828880b817fdcfc4c63dcd3 extends Twig_Template
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
        echo "    <p>A visitor has sent in a story:</p>
    <p>Visitor details:</p>
    <p>Name: <strong>";
        // line 6
        echo twig_escape_filter($this->env, (isset($context["name"]) ? $context["name"] : null), "html", null, true);
        echo "</strong><br/>
    Email: <strong>";
        // line 7
        echo twig_escape_filter($this->env, (isset($context["email"]) ? $context["email"] : null), "html", null, true);
        echo "</strong><br/>
    Location: <strong>";
        // line 8
        echo twig_escape_filter($this->env, (isset($context["area"]) ? $context["area"] : null), "html", null, true);
        echo "</strong><br/>
    IP Address: <strong>";
        // line 9
        echo twig_escape_filter($this->env, (isset($context["ip_address"]) ? $context["ip_address"] : null), "html", null, true);
        echo "</strong></p>
    <p>Their message:</p>
    <p><strong>";
        // line 11
        echo nl2br(twig_escape_filter($this->env, (isset($context["message"]) ? $context["message"] : null), "html", null, true));
        echo "</strong></p>
";
    }

    public function getTemplateName()
    {
        return "emails/story.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  52 => 11,  47 => 9,  43 => 8,  39 => 7,  35 => 6,  31 => 4,  28 => 3,);
    }
}
