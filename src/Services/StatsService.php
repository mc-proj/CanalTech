<?php

namespace App\Services;

class StatsService {

    public function gereStats($stats) {
        $nb_heures = ($stats["total_heures"] - $stats["total_jours"] * 24);
        $nb_jours = intval($nb_heures/24);
        $nb_heures -= $nb_jours*24;
        $stats["total_jours"] += $nb_jours;

        $temps_moyen_journalier = 0;
        $nombre_jours_concernes = $stats["total_jours"];

        if($stats["total_heures"] > 0) {
            $nombre_jours_concernes += 1;
        }

        if($nombre_jours_concernes > 0) {
            //le calcul concidere 1 journée de travail = 7h
            $heures_par_journee = 7;
            $temps_moyen_journalier = (($stats["total_jours"] * $heures_par_journee) + ($stats["total_heures"] - $stats["total_jours"] * 24))/$nombre_jours_concernes;
            $temps_moyen_journalier = round($temps_moyen_journalier, 1);
        } else {
            $temps_moyen_journalier = 0;
        }

        return $stats = [
                    "nombre de taches effectuees" => $stats["nombre_finies"],
                    "temps total taches" => $stats["total_jours"] . " jour(s) et " . $nb_heures . " heure(s)",
                    "temps moyen par jour" => $temps_moyen_journalier . " heure(s)"
        ];
    }
}