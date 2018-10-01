<?php

/* emails/directory_request.html.twig */
class __TwigTemplate_3245e6ab28ff97aef234ca135a421e8c extends Twig_Template
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
        echo "    <p>A visitor has sent in a directory request:</p>
    <p>Visitor details:</p>
    <p>Name: <strong>";
        // line 6
        echo twig_escape_filter($this->env, (isset($context["name"]) ? $context["name"] : null), "html", null, true);
        echo "</strong><br/>
    Email: <strong>";
        // line 7
        echo twig_escape_filter($this->env, (isset($context["email"]) ? $context["email"] : null), "html", null, true);
        echo "</strong><br/>
    Position: <strong>";
        // line 8
        echo twig_escape_filter($this->env, (isset($context["position"]) ? $context["position"] : null), "html", null, true);
        echo "</strong><br/>
    Church/Organisation: <strong>";
        // line 9
        echo twig_escape_filter($this->env, (isset($context["church"]) ? $context["church"] : null), "html", null, true);
        echo "</strong><br/>
    Website: <strong>";
        // line 10
        echo twig_escape_filter($this->env, (isset($context["url"]) ? $context["url"] : null), "html", null, true);
        echo "</strong><br/>
    IP Address: <strong>";
        // line 11
        echo twig_escape_filter($this->env, (isset($context["ip_address"]) ? $context["ip_address"] : null), "html", null, true);
        echo "</strong></p>
    <p>Their Address:</p>
    <p><strong>";
        // line 13
        echo nl2br(twig_escape_filter($this->env, (isset($context["address"]) ? $context["address"] : null), "html", null, true));
        echo "</strong></p>
";
    }

    public function getTemplateName()
    {
        return "emails/directory_request.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  60 => 13,  55 => 11,  51 => 10,  47 => 9,  43 => 8,  39 => 7,  35 => 6,  31 => 4,  28 => 3,);
    }
}
