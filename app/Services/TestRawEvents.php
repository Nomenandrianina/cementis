<?php

namespace App\Services;

/**
 * Données GPS brutes statiques pour tester le calcul de rotation.
 *
 * Circuit simulé : Andranomena → Andranomena (boucle complète)
 *
 * Séquence des étapes configurées :
 *   T1  enter_zone       Andranomena
 *   T2  leave_zone       Andranomena
 *   CP  pass_checkpoint  Check point Ambodimita
 *   CP  pass_checkpoint  Check point Ambohitrimanjaka
 *   CP  pass_checkpoint  Check point Ampasika
 *   CP  pass_checkpoint  Check point MBS
 *   T3  enter_zone       Ilanivato
 *   --  enter_zone       Garage          (séjour intermédiaire)
 *   --  leave_zone       Parking ilanivato (séjour intermédiaire)
 *   T4  leave_zone       Ilanivato
 *   CP  pass_checkpoint  Check point MBS
 *   CP  pass_checkpoint  Check point Ampasika
 *   CP  pass_checkpoint  Check point Ambohitrimanjaka
 *   CP  pass_checkpoint  Check point Ambodimita
 *   T5  enter_zone       Andranomena
 *
 * Format identique au retour de OBJECT_GET_EVENTS :
 *   [0] type, [1] description, [2] imei, [3] name, [4] dt,
 *   [5] lat, [6] lng, [7] altitude, [8] angle, [9] speed, [10] params{}
 */
class TestRawEvents
{
    public const IMEI = '865135061356851';
    public const NAME = 'Sorento_Tsihadino';

    /**
     * Retourne un tableau de rawEvents simulant une rotation COMPLÈTE et VALIDE.
     * La rotation commence le 2026-04-01 à 05:03 et se termine à 08:20.
     *
     * Des événements parasites (stopped, GPS noise) sont intercalés pour
     * valider la robustesse du filtre dans GpsEventMapper.
     */
    public static function completeRotationTsiadino(): array
    {
        $imei   = self::IMEI;
        $name   = self::NAME;
        $params = self::params();
        return [
            // ── T1 : Entrée zone Andranomena ────────────────────────────────
            ['zone_in', 'Entrée zone (Andranomena)', $imei, $name,
             '2026-04-01 05:03:36', '-18.855324', '47.480787', '0', '71', '7', $params],

            // ── T2 : Sortie zone Andranomena ────────────────────────────────
            ['zone_out', 'sortie zone (Andranomena)', $imei, $name,
             '2026-04-01 06:46:27', '-18.865449', '47.486018', '0', '162', '9', $params],

            // ── CP Ambodimita (aller) ────────────────────────────────────────
            ['marker_in', 'Passage Check point  (Check point Ambodimita)', $imei, $name,
             '2026-04-01 06:47:10', '-18.865449', '47.486018', '0', '162', '9', $params],


            // ── CP Ambohitrimanjaka (aller) ──────────────────────────────────
            ['marker_in', 'Passage Check point  (Check point Ambohitrimanjaka)', $imei, $name,
             '2026-04-01 07:03:20', '-18.879464', '47.480818', '0', '221', '21', $params],

            // ── CP Ampasika (aller) ──────────────────────────────────────────
            ['marker_in', 'Passage Check point  (Check point Ampasika)', $imei, $name,
             '2026-04-01 07:21:16', '-18.910282', '47.498822', '0', '37', '15', $params],

            // ── CP MBS (aller) ───────────────────────────────────────────────
            ['marker_in', 'Check point entrant (Check point MBS)', $imei, $name,
             '2026-04-01 07:40:14', '-18.930304', '47.496231', '0', '227', '26', $params],

            // ── T3 : Entrée zone Ilanivato ──────────────────────────────────
            ['zone_in', 'Entrée zone (Ilanivato)', $imei, $name,
             '2026-04-01 07:49:31', '-18.925798', '47.499804', '0', '38', '9', $params],

            // ── Garage (séjour intermédiaire dans Ilanivato) ─────────────────
            ['zone_in', 'Entrée zone (Garage)', $imei, $name,
             '2026-04-01 07:52:00', '-18.924799', '47.499433', '0', '90', '5', $params],
            ['stopped', 'Arrêt plus de 10mn', $imei, $name,
             '2026-04-01 07:55:00', '-18.924799', '47.499500', '0', '0', '0', $params],
            ['zone_out', 'sortie zone (Garage)', $imei, $name,
             '2026-04-01 08:05:00', '-18.924900', '47.499600', '0', '80', '4', $params],

            // ── Parking Ilanivato (séjour intermédiaire) ─────────────────────
            ['zone_in', 'Entrée zone (Parking ilanivato)', $imei, $name,
             '2026-04-01 08:06:00', '-18.922186', '47.499905', '0', '45', '3', $params],
            ['stopped', 'Arrêt plus de 10mn', $imei, $name,
             '2026-04-01 08:08:00', '-18.922186', '47.499905', '0', '0', '0', $params],
            ['zone_out', 'sortie zone (Parking ilanivato)', $imei, $name,
             '2026-04-01 08:15:00', '-18.922323', '47.500044', '0', '60', '5', $params],

            // ── T4 : Sortie zone Ilanivato ──────────────────────────────────
            ['zone_out', 'sortie zone (Ilanivato)', $imei, $name,
             '2026-04-01 08:30:09', '-18.921609', '47.501147', '0', '68', '8', $params],

            // ── CP MBS (retour) ──────────────────────────────────────────────
            ['marker_in', 'Passage Check point  (Check point MBS)', $imei, $name,
             '2026-04-01 08:46:43', '-18.930658', '47.494987', '0', '134', '31', $params],

            // ── CP Ampasika (retour) ─────────────────────────────────────────
            ['marker_in', 'Passage Check point  (Check point Ampasika)', $imei, $name,
             '2026-04-01 08:50:14', '-18.908993', '47.498973', '0', '198', '7', $params],

            // ── CP Ambohitrimanjaka (retour) ─────────────────────────────────
            ['marker_in', 'Passage Check point  (Check point Ambohitrimanjaka)', $imei, $name,
             '2026-04-01 08:55:16', '-18.879909', '47.480933', '0', '333', '9', $params],

            // ── CP Ambodimita (retour) ───────────────────────────────────────
            ['marker_in', 'Passage Check point  (Check point Ambodimita)', $imei, $name,
             '2026-04-01 09:00:27', '-18.865449', '47.486018', '0', '162', '9', $params],

            // ── T5 : Arrivée Andranomena (fin rotation) ──────────────────────
            ['zone_in', 'Entrée zone (Andranomena)', $imei, $name,
             '2026-04-01 09:20:00', '-18.855324', '47.480787', '0', '71', '5', $params],

            // ── Événements APRÈS T5 (bruit à ignorer) ──────────────────────
            ['stopped', 'Arrêt plus de 10mn', $imei, $name,
             '2026-04-01 09:30:00', '-18.855800', '47.481000', '0', '0', '0', $params],

             ['zone_out', 'sortie zone (Andranomena)', $imei, $name,
             '2026-04-01 10:00:00', '-18.865449', '47.486018', '0', '162', '9', $params],
            // CP Ambodimita
            ['marker_in', 'Passage Check point  (Check point Ambodimita)', $imei, $name,
             '2026-04-01 10:10:10', '-18.865449', '47.486018', '0', '162', '9', $params],
            // CP Ambohitrimanjaka
            ['marker_in', 'Passage Check point  (Check point Ambohitrimanjaka)', $imei, $name,
             '2026-04-01 10:15:20', '-18.879464', '47.480818', '0', '221', '21', $params],
            // ← s'arrête ici, pas de Ampasika, MBS ni Ilanivato
            ['stopped', 'Arrêt plus de 10mn', $imei, $name,
             '2026-04-01 10:30:00', '-18.879464', '47.480818', '0', '0', '0', $params],
        ];
    }

    public static function completeRotationAntonio(): array
    {
        $imei   = self::IMEI;
        $name   = self::NAME;
        $params = self::params();
        return [
            // ── DÉPART : Zone Andoharanofotsy ──────────────────────────────────────
            ['zone_in', 'Entrée zone (Andoharanofotsy)', $imei, $name,
            '2026-04-01 18:15:00', '-18.9667', '47.5333', '0', '0', '0', $params],

            ['zone_in', 'Entrée zone (Garage Antonio)', $imei, $name,
            '2026-04-01 18:20:00', '-18.9667', '47.5333', '0', '0', '0', $params],

            ['zone_out', 'Sortie zone (Garage Antonio)', $imei, $name,
            '2026-04-02 07:00:00', '-18.9650', '47.5320', '0', '45', '12', $params],

            ['zone_out', 'Sortie zone (Andoharanofotsy)', $imei, $name,
            '2026-04-02 07:05:00', '-18.9650', '47.5320', '0', '45', '12', $params],

            // ── ALLER : Andoharanofotsy -> Andranomena ──────────────────────────────
            // CP Ankandimbahoaka
            ['marker_in', 'Passage Check point (Ankadimbahoaka)', $imei, $name,
            '2026-04-02 07:25:00', '-18.9500', '47.5200', '0', '50', '25', $params],

            // CP MBS
            ['marker_in', 'Passage Check point (MBS)', $imei, $name,
            '2026-04-02 07:45:00', '-18.9303', '47.4962', '0', '55', '30', $params],

            // CP Ampasika
            ['marker_in', 'Passage Check point (Ampasika)', $imei, $name,
            '2026-04-02 07:55:00', '-18.9102', '47.4988', '0', '40', '20', $params],

            // CP Ambohitrimanjaka
            ['marker_in', 'Passage Check point (Ambohitrimanjaka)', $imei, $name,
            '2026-04-02 08:00:00', '-18.8794', '47.4808', '0', '60', '35', $params],

            // ── DESTINATION : Zone Andranomena ──────────────────────────────────────
            ['zone_in', 'Entrée zone (Andranomena)', $imei, $name,
            '2026-04-02 08:05:00', '-18.8553', '47.4807', '0', '30', '15', $params],

            ['zone_in', 'Entrée zone (M-TEC)', $imei, $name,
            '2026-04-02 08:10:00', '-18.8553', '47.4807', '0', '30', '15', $params],

            ['zone_out', 'Sortie zone (M-TEC)', $imei, $name,
            '2026-04-02 17:10:00', '-18.8553', '47.4807', '0', '45', '10', $params],

            ['zone_out', 'Sortie zone (Andranomena)', $imei, $name,
            '2026-04-02 17:20:00', '-18.8553', '47.4807', '0', '45', '10', $params],

            // ── RETOUR : Andranomena -> Andoharanofotsy ─────────────────────────────
            // CP Ambohitrimanjaka (Retour)
            ['marker_in', 'Passage Check point (Ambohitrimanjaka)', $imei, $name,
            '2026-04-02 17:25:00', '-18.8794', '47.4808', '0', '58', '28', $params],

            // CP Ampasika (Retour)
            ['marker_in', 'Passage Check point (Ampasika)', $imei, $name,
            '2026-04-02 17:45:00', '-18.9102', '47.4988', '0', '52', '22', $params],

            // CP MBS (Retour)
            ['marker_in', 'Passage Check point (MBS)', $imei, $name,
            '2026-04-02 17:55:00', '-18.9303', '47.4962', '0', '48', '31', $params],

            // CP Ankandimbahoaka (Retour)
            ['marker_in', 'Passage Check point (Ankadimbahoaka)', $imei, $name,
            '2026-04-02 18:00:00', '-18.9500', '47.5200', '0', '55', '24', $params],

            // ── FIN DE ROTATION : Retour Andoharanofotsy ───────────────────────────
            ['zone_in', 'Entrée zone (Andoharanofotsy)', $imei, $name,
            '2026-04-02 18:20:00', '-18.9667', '47.5333', '0', '20', '5', $params],
            // R1 valide
            //
            ['zone_in', 'Entrée zone (Garage Antonio)', $imei, $name,
            '2026-04-02 18:30:00', '-18.9667', '47.5333', '0', '0', '0', $params],

            ['zone_out', 'Sortie zone (Garage Antonio)', $imei, $name,
            '2026-04-03 07:10:00', '-18.9650', '47.5320', '0', '45', '12', $params],

            ['zone_out', 'Sortie zone (Andoharanofotsy)', $imei, $name,
            '2026-04-03 07:15:00', '-18.9650', '47.5320', '0', '45', '12', $params],

            // ── ALLER : Andoharanofotsy -> Andranomena ──────────────────────────────
            // CP Ankandimbahoaka
            ['marker_in', 'Passage Check point (Ankadimbahoaka)', $imei, $name,
            '2026-04-03 07:30:00', '-18.9500', '47.5200', '0', '50', '25', $params],

            // CP MBS
            ['marker_in', 'Passage Check point (MBS)', $imei, $name,
            '2026-04-03 07:50:00', '-18.9303', '47.4962', '0', '55', '30', $params],

            // CP Ampasika
            ['marker_in', 'Passage Check point (Ampasika)', $imei, $name,
            '2026-04-03 08:00:00', '-18.9102', '47.4988', '0', '40', '20', $params],

            // CP Ambohitrimanjaka
            ['marker_in', 'Passage Check point (Ambohitrimanjaka)', $imei, $name,
            '2026-04-03 08:10:00', '-18.8794', '47.4808', '0', '60', '35', $params],

            // ── DESTINATION : Zone Andranomena ──────────────────────────────────────
            ['zone_in', 'Entrée zone (Andranomena)', $imei, $name,
            '2026-04-03 08:15:00', '-18.8553', '47.4807', '0', '30', '15', $params],

            ['zone_in', 'Entrée zone (M-TEC)', $imei, $name,
            '2026-04-03 08:20:00', '-18.8553', '47.4807', '0', '30', '15', $params],

            ['zone_out', 'Sortie zone (M-TEC)', $imei, $name,
            '2026-04-03 17:15:00', '-18.8553', '47.4807', '0', '45', '10', $params],

            ['zone_out', 'Sortie zone (Andranomena)', $imei, $name,
            '2026-04-03 17:20:00', '-18.8553', '47.4807', '0', '45', '10', $params],

            // ── RETOUR : Andranomena -> Andoharanofotsy ─────────────────────────────
            // CP Ambohitrimanjaka (Retour)
            ['marker_in', 'Passage Check point (Ambohitrimanjaka)', $imei, $name,
            '2026-04-03 17:30:00', '-18.8794', '47.4808', '0', '58', '28', $params],

            // CP Ampasika (Retour)
            ['marker_in', 'Passage Check point (Ampasika)', $imei, $name,
            '2026-04-03 17:45:00', '-18.9102', '47.4988', '0', '52', '22', $params],

            // CP MBS (Retour)
            ['marker_in', 'Passage Check point (MBS)', $imei, $name,
            '2026-04-03 17:55:00', '-18.9303', '47.4962', '0', '48', '31', $params],

            // CP Ankandimbahoaka (Retour)
            ['marker_in', 'Passage Check point (Ankadimbahoaka)', $imei, $name,
            '2026-04-03 18:00:00', '-18.9500', '47.5200', '0', '55', '24', $params],

            // ── FIN DE ROTATION : Retour Andoharanofotsy ───────────────────────────
            ['zone_in', 'Entrée zone (Andoharanofotsy)', $imei, $name,
            '2026-04-03 18:30:00', '-18.9667', '47.5333', '0', '20', '5', $params],

        ];
    }

    /**
     * Rotation INCOMPLÈTE : le véhicule n'atteint pas Ilanivato.
     * Doit produire status = 'in_progress' ou 'cancelled'.
     */
    public static function incompleteRotation(): array
    {
        $imei   = self::IMEI;
        $name   = self::NAME;
        $params = self::params();

        return [
            // T1
            ['zone_in', 'Entrée zone (Andranomena)', $imei, $name,
             '2026-04-01 05:03:36', '-18.855324', '47.480787', '0', '71', '7', $params],
            // T2
            ['zone_out', 'sortie zone (Andranomena)', $imei, $name,
             '2026-04-01 06:46:27', '-18.865449', '47.486018', '0', '162', '9', $params],
            // CP Ambodimita
            ['marker_in', 'Passage Check point  (Check point Ambodimita)', $imei, $name,
             '2026-04-01 06:47:10', '-18.865449', '47.486018', '0', '162', '9', $params],
            // CP Ambohitrimanjaka
            ['marker_in', 'Passage Check point  (Check point Ambohitrimanjaka)', $imei, $name,
             '2026-04-01 07:03:20', '-18.879464', '47.480818', '0', '221', '21', $params],
            // ← s'arrête ici, pas de Ampasika, MBS ni Ilanivato
            ['stopped', 'Arrêt plus de 10mn', $imei, $name,
             '2026-04-01 07:30:00', '-18.879464', '47.480818', '0', '0', '0', $params],
        ];
    }

    /**
     * Rotation ANNULÉE : le véhicule dévie vers une zone non prévue dans le circuit.
     * Doit produire status = 'cancelled'.
     */
    public static function cancelledRotation(): array
    {
        $imei   = self::IMEI;
        $name   = self::NAME;
        $params = self::params();

        return [
            // T1
            ['zone_in', 'Entrée zone (Andranomena)', $imei, $name,
             '2026-04-01 05:03:36', '-18.855324', '47.480787', '0', '71', '7', $params],
            // T2
            ['zone_out', 'sortie zone (Andranomena)', $imei, $name,
             '2026-04-01 06:46:27', '-18.865449', '47.486018', '0', '162', '9', $params],
            // Déviation : entre dans M-TEC (zone hors circuit) → annulation
            ['zone_in', 'Entrée zone (M-TEC )', $imei, $name,
             '2026-04-01 06:50:00', '-18.855051', '47.480920', '0', '90', '5', $params],
        ];
    }

    /**
     * Simule les données réelles brutes du 2026-04-01 fournies dans les specs.
     * Correspond à une DEMI-rotation (aller seulement, retour non inclus dans l'extrait).
     */
    public static function realApiSample(): array
    {
        $imei   = self::IMEI;
        $name   = 'Sorento_Tsihadino ';
        $p      = self::params();

        return [
            ['zone_out','sortie zone (Ilanivato)',$imei,$name,'2026-04-01 04:34:14','-18.92748','47.498796','0','220','13',$p],
            ['marker_in','Check point entrant (Check point MBS)',$imei,$name,'2026-04-01 04:35:33','-18.930304','47.496231','0','227','26',$p],
            ['marker_out','Check point sortant (Check point MBS)',$imei,$name,'2026-04-01 04:36:22','-18.928498','47.494156','0','334','50',$p],
            ['marker_in','Check point entrant (Check point Ampasika)',$imei,$name,'2026-04-01 04:39:25','-18.910282','47.498822','0','37','15',$p],
            ['marker_out','Check point sortant (Check point Ampasika)',$imei,$name,'2026-04-01 04:42:35','-18.908593','47.498973','0','13','8',$p],
            ['marker_in','Check point entrant (Check point Ambohitrimanjaka)',$imei,$name,'2026-04-01 04:54:51','-18.879556','47.480738','0','342','34',$p],
            ['marker_out','Check point sortant (Check point Ambohitrimanjaka)',$imei,$name,'2026-04-01 04:55:06','-18.87848','47.481591','0','39','30',$p],
            ['zone_in','Entrée zone (Andranomena)',$imei,$name,'2026-04-01 05:03:36','-18.855324','47.480787','0','71','7',$p],
            ['stopped','Arrêt plus de 10mn',$imei,$name,'2026-04-01 05:09:19','-18.8548','47.481347','0','243','0',$p],
            ['marker_in','Passage Check point  (Check point Ambodimita)',$imei,$name,'2026-04-01 06:46:27','-18.865449','47.486018','0','162','9',$p],
            ['zone_out','sortie zone (Andranomena)',$imei,$name,'2026-04-01 06:46:27','-18.865449','47.486018','0','162','9',$p],
            ['marker_in','Passage Check point  (Check point Ambohitrimanjaka)',$imei,$name,'2026-04-01 07:03:20','-18.879464','47.480818','0','221','21',$p],
            ['marker_in','Passage Check point  (Check point Ambohitrimanjaka)',$imei,$name,'2026-04-01 07:21:16','-18.879909','47.480933','0','333','9',$p],
            ['marker_in','Passage Check point  (Check point Ampasika)',$imei,$name,'2026-04-01 07:40:14','-18.908993','47.498973','0','198','7',$p],
            ['marker_in','Passage Check point  (Check point MBS)',$imei,$name,'2026-04-01 07:46:43','-18.930658','47.494987','0','134','31',$p],
            ['zone_in','Entrée zone (Ilanivato)',$imei,$name,'2026-04-01 07:49:31','-18.925798','47.499804','0','38','9',$p],
            ['zone_out','sortie zone (Ilanivato)',$imei,$name,'2026-04-01 08:02:09','-18.921609','47.501147','0','68','8',$p],
            ['stopped','Arrêt plus de 10mn',$imei,$name,'2026-04-01 09:31:50','-18.861091','47.558449','0','184','0',$p],
            ['stopped','Arrêt plus de 10mn',$imei,$name,'2026-04-01 13:08:51','-18.802824','47.58988','0','227','0',$p],
            ['stopped','Arrêt plus de 10mn',$imei,$name,'2026-04-01 15:20:21','-18.88828','47.5226','0','188','0',$p],
            ['stopped','Arrêt plus de 10mn',$imei,$name,'2026-04-01 16:17:17','-18.905111','47.509191','0','189','0',$p],
            ['zone_in','Entrée zone (Ilanivato)',$imei,$name,'2026-04-01 16:35:27','-18.92218','47.499987','0','25','0',$p],
            ['zone_out','sortie zone (Parking ilanivato)',$imei,$name,'2026-04-01 16:44:21','-18.924802','47.499813','0','19','0',$p],
            ['stopped','Arrêt plus de 10mn',$imei,$name,'2026-04-01 16:47:20','-18.924787','47.499778','0','103','0',$p],
            ['stopped','Arrêt plus de 10mn',$imei,$name,'2026-04-01 16:47:20','-18.924787','47.499778','0','103','0',$p],
        ];
    }

    /** Params GPS génériques communs à tous les événements de test */
    private static function params(): array
    {
        return [
            'acc'     => '1',
            'alarm'   => '0',
            'batl'    => '6',
            'bats'    => '1',
            'cellid'  => '10283',
            'defense' => '0',
            'gpslev'  => '9',
            'gsmlev'  => '3',
            'iccid'   => '8926102411710195904F',
            'lac'     => '1030',
            'mcc'     => '646',
            'mnc'     => '2',
            'odo'     => '119834.157',
            'pump'    => '0',
            'track'   => '1',
        ];
    }
}
