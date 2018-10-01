<?php

/* emails/register_confirmation.html.twig */
class __TwigTemplate_c3d2f863567406d4c95e1298c858a4ee extends Twig_Template
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
        echo "    <p><strong>Hello ";
        echo twig_escape_filter($this->env, (isset($context["first_name"]) ? $context["first_name"] : null), "html", null, true);
        echo ", welcome to Christian Flatshare!</strong></p>
    <p>This is an email to confirm that your email address has been registered on Christian Flatshare.</p>
    <p>Your login details are:</p>
    <p>Email: <strong>";
        // line 7
        echo twig_escape_filter($this->env, (isset($context["email"]) ? $context["email"] : null), "html", null, true);
        echo "</strong><br/>Password: <strong>*****</strong></p>
    <p>If this registration is without your permission, please reply to this email to have your account removed.</p>
";
    }

    public function getTemplateName()
    {
        return "emails/register_confirmation.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  38 => 7,  31 => 4,  28 => 3,);
    }
}
