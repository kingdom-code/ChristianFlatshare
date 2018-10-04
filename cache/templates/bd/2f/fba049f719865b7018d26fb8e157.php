<?php

/* emails/change_email.html.twig */
class __TwigTemplate_bd2ffba049f719865b7018d26fb8e157 extends Twig_Template
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
        echo "    <p><strong>Hi ";
        echo twig_escape_filter($this->env, (isset($context["first_name"]) ? $context["first_name"] : null), "html", null, true);
        echo ",</strong></p>
    <p>This is an email to confirm that your registered email address on Christian Flatshare has changed.</p>
    <p>Changed to: <strong>";
        // line 6
        echo twig_escape_filter($this->env, (isset($context["new_email"]) ? $context["new_email"] : null), "html", null, true);
        echo "</strong><br />Previously: <strong>";
        echo twig_escape_filter($this->env, (isset($context["old_email"]) ? $context["old_email"] : null), "html", null, true);
        echo "</strong></p>
    <p>If this new email address has been registered without your consent, please  reply to this email to have your account removed.</p>
";
    }

    public function getTemplateName()
    {
        return "emails/change_email.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  37 => 6,  31 => 4,  28 => 3,);
    }
}
