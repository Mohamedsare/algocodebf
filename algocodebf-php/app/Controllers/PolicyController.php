<?php

class PolicyController extends Controller
{
    /**
     * Page Politique de confidentialité
     */
    public function privacy()
    {
        $data = [
            'title' => 'Politique de Confidentialité - AlgoCodeBF',
            'meta_description' => 'Découvrez comment AlgoCodeBF protège vos données personnelles et respecte votre vie privée. Politique de confidentialité complète et transparente.',
            'meta_keywords' => 'politique confidentialité, protection données, vie privée, RGPD, AlgoCodeBF'
        ];

        $this->view('policy/privacy', $data);
    }

    /**
     * Page Conditions d'utilisation
     */
    public function terms()
    {
        $data = [
            'title' => 'Conditions d\'Utilisation - AlgoCodeBF',
            'meta_description' => 'Consultez les conditions d\'utilisation de la plateforme AlgoCodeBF. Règles et conditions pour une utilisation responsable de notre communauté.',
            'meta_keywords' => 'conditions utilisation, règles communauté, AlgoCodeBF, CGU'
        ];

        $this->view('policy/terms', $data);
    }

    /**
     * Page Mentions légales
     */
    public function legal()
    {
        $data = [
            'title' => 'Mentions Légales - AlgoCodeBF',
            'meta_description' => 'Mentions légales et informations sur AlgoCodeBF. Identification de l\'éditeur, hébergeur et conditions d\'utilisation.',
            'meta_keywords' => 'mentions légales, éditeur, hébergeur, AlgoCodeBF'
        ];

        $this->view('policy/legal', $data);
    }
}
