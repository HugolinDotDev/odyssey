<?php
/**
 * Class to cover flow consistency between BPMN file and MFC file
 * @author Simon Boutrouille, Amaury Denis, Kamelia Brahimi, Thileli Saci, Zineb Brahimi
 */

namespace App\Lib\Coverage;

use App\Lib\BPMNIO\BPMNAnalyzer;
use App\Lib\DrawIO\MCFAnalyzer;

class FlowCoverage {

    /**
     * @var App\Lib\BPMNIO\BPMNAnalyzer
     */
    private $bpmn_analyzer;

    /**
     * @var App\Lib\DrawIO\MCFAnalyzer;
     */
    private $mcf_analyzer;

    public function __construct(string $bpmn_content, string $mfc_content) {
        $this->bpmn_analyzer = new BPMNAnalyzer;
        $this->mcf_analyzer = new MCFAnalyzer;

        $this->bpmn_analyzer->loadFileContent($bpmn_content);
        $this->mcf_analyzer->loadFileContent($mfc_content);
    }

    public function analyzeCoverage() : array {
        $bpmn_flows = $this->bpmn_analyzer->getFlows();
        $mcf_flows = $this->mcf_analyzer->getFlows();

        return $this->analyzeConsistency($bpmn_flows, $mcf_flows);
    }

    /**
     * Checks consistency between bpmn'flows and mfc's flows
     * @param array $bpmnFlows the list of bpmn flows
     * @param array $mfcFlows the list of mfc flows
     * @return array An array containing the coverage percentage and a list of feedbacks
     */
    private function analyzeConsistency(array $bpmn_flows, array $mfc_flows) : array {
        if (count($bpmn_flows) == 0) {
            return array(
                "Coverage" => 0, "Feedbacks" => array("Le fichier BPMN est vide ou n'est pas du bon format.")
            );
        }
        if (count($mfc_flows) == 0) {
            return array(
                "Coverage" => 0, "Feedbacks" => array("Le fichier MCF est vide ou n'est pas du bon format.")
            );
        }
        $number_covered = 0;
        $different_size = false;
        $missing_bpmn_flow_in_mcf = array();
        $missing_mcf_flow_in_bpmn = array();

        $result = array("Coverage" => 0, "Feedbacks" => array());
        
        if (count($bpmn_flows) != count($mfc_flows))
           $different_size = true;

        foreach ($bpmn_flows as $bpmn_flow) {
            $covered = false;
            foreach ($mfc_flows as $mfc_flow) {
                if ($bpmn_flow == $mfc_flow) {
                    $number_covered++;
                    $covered = true;
                }
            }
            if (!$covered)
                array_push($missing_bpmn_flow_in_mcf, $bpmn_flow);
        }

        foreach($mfc_flows as $mfc_flow) {
            $covered = false;
            foreach ($bpmn_flows as $bpmn_flow) {
                if ($mfc_flow == $bpmn_flow)
                    $covered = true;
            }
            if (!$covered)
                array_push($missing_mcf_flow_in_bpmn, $mfc_flow);
        }

        if ($number_covered == count($bpmn_flows) && $different_size == false) {
            $result["Coverage"] = 100;
            array_push($result["Feedbacks"], "Aucun problème détecté, tous les flux sont présents dans le bpmn");
            return $result;
        }

        else {
            if ($different_size)
                array_push($result["Feedbacks"], "Nombre de flux différent entre les modèles");
            
            $size = max(count($bpmn_flows), count($mfc_flows));
            $result["Coverage"] =  $number_covered*100/$size;

            foreach ($missing_bpmn_flow_in_mcf as $flow)
                array_push ($result["Feedbacks"], "Le flux {$flow} du BPMN n'apparait pas dans le MCF");

            foreach ($missing_mcf_flow_in_bpmn as $flow)
                array_push ($result["Feedbacks"], "Le flux {$flow} du MCF n'apparait pas dans le BPMN");
            
            return $result;
        } 
    }

}