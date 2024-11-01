<?php
namespace SweetAnalytics;

class SweetAnalyticsDefs
{
    private $defsArray = array();
    private $about = array();

    public function __construct()
    {
        $this->defsArray['es_ES'] = array(
            'Dashboard' => 'Dashboard',
            'Settings' => 'Ajustes',
            'About us' => 'Acerca de',
            'Site name' => 'Nombre',
            'Domain' => 'Dominio',
            'Platform' => 'Plataforma',
            'Extension' => 'Extensión',
            'Currency' => 'Moneda',
            'Write your message here' => 'Escribe tu mensaje aquí',
        );

        $this->about['es_ES'] = '<p>Sweet Analytics ofrece toda la analítica sobre tu negocio incluyendo el rendimiento
        sobre todas tus actividades de marketing. Esto abre la oportunidad a analizar el impacto y poder optimizar cada canal
        para mejorar las ventas. Este plugin permite activar el Sweet Analytics Tracker y la funcionalidad de Enhanced Ecommerce,
        la cual proporciona información sobre el comportamiento de los usuarios que llegan a tu página, atribución y muchísimo más.
        ¡Gracias por unirte a esta aventura que vamos a compartir!<br><br>- El equipo de Sweet Analytics</p>';

        $this->about['default'] = '<p>Sweet Analytics provides all the analytics and insights you need to run your business,
        manage your marketing activities, and fully understand the impact of each activity to take. This plugin allows you
        activate the Sweet Analytics Tracker and Enhanced Ecommmerce features, which provides behavioural analysis, attribution,
        and much much more.<br><br>We thank you for joining this adventure with us!<br><br>- The Sweet Analytics Team</p>';
    }

    public function getDef($text)
    {
        $language = get_locale();
        if (isset($this->defsArray[$language][$text])) {
            return $this->defsArray[$language][$text];
        }
        return $text;
    }

    public function getAbout()
    {
        $language = get_locale();

        if (isset($this->about[$language])) {
            return $this->about[$language];
        }
        return $this->about['default'];
    }
}
