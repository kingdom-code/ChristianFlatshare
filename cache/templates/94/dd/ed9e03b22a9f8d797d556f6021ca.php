<?php

/* emails/contact.html.twig */
class __TwigTemplate_94dded9e03b22a9f8d797d556f6021ca extends Twig_Template
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
        echo "    <p>Christian Flatshare website feedback.</p>
    <p>Visitor details:</p>
    <p>Name: <strong>";
        // line 6
        echo twig_escape_filter($this->env, (isset($context["name"]) ? $context["name"] : null), "html", null, true);
        echo "</strong><br/>
    Email: <strong>";
        // line 7
        echo twig_escape_filter($this->env, (isset($context["email"]) ? $context["email"] : null), "html", null, true);
        echo "</strong><br/>
    Telephone Number: <strong>";
        // line 8
        echo twig_escape_filter($this->env, (isset($context["telephone_number"]) ? $context["telephone_number"] : null), "html", null, true);
        echo "</strong><br/>
    Church/Organisation: <strong>";
        // line 9
        echo twig_escape_filter($this->env, (isset($context["church"]) ? $context["church"] : null), "html", null, true);
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
        return "emails/contact.html.twig";
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
