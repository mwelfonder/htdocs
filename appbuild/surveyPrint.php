<!DOCTYPE html>
<html>
  <div class="bp-body">
    <div id="pagewrapper">
      <!-- page 1 start -->
      <div id="bp_page_1" style="padding: 5mm">
        <h3
          style="
            text-align: center;
            background: #eeece1;
            margin: 0;
            padding: 20px;
            color: #767171;
          "
        >
          Begehungsprotokoll Hausanschluss
        </h3>
        <table class="bp-table">
          <tr class="bp-bg-dark">
            <td colspan="5"><b>Adresse des Anschlusses</b></td>
          </tr>
          <tr class="bp-bg-b">
            <td class="bp-w40"><b>Straße</b></td>
            <td class="bp-w10"><b>Nr.</b></td>
            <td class="bp-w5"></td>
            <!-- Empty space cell -->
            <td class="bp-w10"><b>PLZ</b></td>
            <td class="bp-w35"><b>Ort</b></td>
          </tr>
          <tr class="bp-bg-b" style="height: 22px">
            <td class="bp-w40" id="bp_street"></td>
            <td class="bp-w10" id="bp_streetnumber"></td>
            <td class="bp-w5"></td>
            <!-- Empty space cell -->
            <td class="bp-w10" id="bp_zip"></td>
            <td class="bp-w35" id="bp_city"></td>
          </tr>
        </table>
        <!-- New table -->
        <table class="bp-table bp-noborder-top" style="">
          <tr class="bp-bg-b">
            <td class="bp-noborder-top bp-w15">
              <b>Gebäudetyp</b>
            </td>
            <td class="bp-noborder-top bp-w15" id="bp_housetyp"></td>
            <td class="bp-noborder-top bp-w15" style="white-space: nowrap">
              <b>Anzahl Wohneinheiten</b>
            </td>
            <td class="bp-noborder-top bp-w15" id="bp_houseunits"></td>
            <td class="bp-noborder-top bp-w15" style="white-space: nowrap">
              <b>Business</b>
            </td>
            <td class="bp-noborder-top bp-w15" id="bp_isbusiness"></td>
          </tr>
        </table>
        <!-- New table -->
        <table class="bp-table bp-noborder-top" style="">
          <tr>
            <td colspan="5" class="bp-bg-dark bp-noborder-top">
              <b>Begehung durchgeführt mit</b>
            </td>
          </tr>
          <tr class="bp-bg-b bp-noborder-top">
            <td colspan="5" class="bp-noborder-top">
              <label style="margin-right: 20px">
                <input
                  type="checkbox"
                  name="verwalter"
                  id="bp_check_verwalter"
                />
                <b>Verwalter</b>
              </label>
              <label style="margin-right: 20px">
                <input
                  type="checkbox"
                  name="eigentuemer"
                  id="bp_check_eigentumer"
                />
                <b>Eigentümer</b>
              </label>
              <label>
                <input
                  type="checkbox"
                  name="bevollmaechtigter"
                  id="bp_check_bevollmaechtigter"
                />
                <b>Bevollmächtigter (z. B. Mieter)</b>
              </label>
            </td>
          </tr>
          <tr class="bp-bg-b" style="height: 22px">
            <td colspan="3" id="bp_durchgefuhrtmitValue"></td>
          </tr>
        </table>
        <!-- New table -->
        <table class="bp-table bp-noborder-top" style="">
          <tr class="bp-bg-dark bp-noborder-top">
            <td class="bp-noborder-top bp-w70"><b>Daten Eigentümer</b></td>
            <td class="bp-noborder-top"><b>Firma</b></td>
          </tr>
          <tr class="bp-bg-b">
            <td class="bp-noborder-top bp-w70">
              <label style="margin-right: 20px">
                <input
                  type="checkbox"
                  name="eigentuemer"
                  id="bp_check_pronoun_herr"
                />
                <b>Herr</b>
              </label>
              <label style="margin-right: 20px">
                <input
                  type="checkbox"
                  name="eigentuemer"
                  id="bp_check_pronoun_frau"
                />
                <b>Frau</b>
              </label>
              <label for="bp_text_titel"><b>Titel:</b></label>
              <input
                type="text"
                name="titel"
                id="bp_check_pronoun_titel"
                placeholder=""
              />
            </td>
            <td id="bp_name_business"></td>
          </tr>
        </table>
        <!-- New table -->
        <table class="bp-table bp-noborder-top" style="">
          <tr class="bp-bg-b bp-noborder-top">
            <td class="bp-noborder-top"><b>Vorname</b></td>
            <td class="bp-noborder-top"><b>Nachmane</b></td>
          </tr>
          <tr class="bp-bg-b bp-noborder-top" style="height: 22px">
            <td class="bp-noborder-top" id="bp_name_firstname_owner"></td>
            <td class="bp-noborder-top" id="bp_name_lastname_owner"></td>
          </tr>
          <tr class="bp-bg-b bp-noborder-top">
            <td class="bp-noborder-top"><b>Telefon</b></td>
            <td class="bp-noborder-top"><b>E-Mail</b></td>
          </tr>
          <tr class="bp-bg-b bp-noborder-top" style="height: 22px">
            <td class="bp-noborder-top" id="bp_name_phone1_owner"></td>
            <td class="bp-noborder-top" id="bp_name_mail1_owner"></td>
          </tr>
        </table>
        <!-- New table -->
        <table class="bp-table bp-noborder-top" style="">
          <tr class="bp-bg-dark bp-noborder-top">
            <td class="bp-noborder-top bp-w70">
              <b>Angaben zum Anschlussnehmer</b>
            </td>
            <td class="bp-noborder-top"><b>Firma</b></td>
          </tr>
          <tr class="bp-bg-b">
            <td class="bp-noborder-top bp-w70">
              <label style="margin-right: 20px">
                <input
                  type="checkbox"
                  name="eigentuemer"
                  id="bp_check_pronoun_herr_tenant"
                />
                <b>Herr</b>
              </label>
              <label style="margin-right: 20px">
                <input
                  type="checkbox"
                  name="eigentuemer"
                  id="bp_check_pronoun_frau_tenant"
                />
                <b>Frau</b>
              </label>
              <label for="bp_text_titel"><b>Titel:</b></label>
              <input
                type="text"
                name="titel"
                id="bp_check_pronoun_titel_tenant"
                placeholder=""
              />
            </td>
            <td id="bp_name_business_tenant"></td>
          </tr>
        </table>
        <!-- New table -->
        <table class="bp-table bp-noborder-top" style="">
          <tr class="bp-bg-b bp-noborder-top">
            <td class="bp-noborder-top"><b>Vorname</b></td>
            <td class="bp-noborder-top"><b>Nachmane</b></td>
          </tr>
          <tr class="bp-bg-b bp-noborder-top" style="height: 22px">
            <td class="bp-noborder-top" id="bp_name_firstname_tenant"></td>
            <td class="bp-noborder-top" id="bp_name_lastname_tenant"></td>
          </tr>
          <tr class="bp-bg-b bp-noborder-top">
            <td class="bp-noborder-top"><b>Telefon</b></td>
            <td class="bp-noborder-top"><b>E-Mail</b></td>
          </tr>
          <tr class="bp-bg-b bp-noborder-top" style="height: 22px">
            <td class="bp-noborder-top" id="bp_phone1_tenant"></td>
            <td class="bp-noborder-top" id="bp_mail1_tenant"></td>
          </tr>
        </table>
        <!-- New table -->
        <table class="bp-table bp-noborder-top" style="">
          <tr class="bp-noborder-top bp-bg-dark">
            <td colspan="5" class="bp-noborder-top">
              <b>Hauseinführung</b>
            </td>
          </tr>
          <tr class="bp-bg-b bp-noborder-top">
            <td colspan="5" class="bp-noborder-top">
              <b>Hauseinführungspunkt wurde festgelegt und markiert im Raum:</b>
            </td>
          </tr>
          <tr class="bp-bg-b">
            <td class="bp-w50" style="font-size: 14px">
              <input type="checkbox" id="HE_fall_1" name="HE_fall" /><label
                for="HE_fall_1"
                >Fall 1: Hauseinführung unterirdisch, Kellergeschoss</label
              >
            </td>
            <td class="bp-w50" style="font-size: 14px">
              <input type="checkbox" id="HE_fall_2" name="HE_fall" /><label
                for="HE_fall_2"
                >Fall 2: Hauseinführung unterirdisch,
                Erdreich-Erdgeschoss</label
              >
            </td>
          </tr>
          <tr class="bp-bg-b">
            <td class="bp-w50" style="font-size: 14px">
              <input type="checkbox" id="HE_fall_3" name="HE_fall" /><label
                for="HE_fall_3"
                >Fall 3: Hauseinführung oberirdisch, Erdgeschoss</label
              >
            </td>
            <td class="bp-w50" style="font-size: 14px">
              <input type="checkbox" id="HE_fall_4" name="HE_fall" /><label
                for="HE_fall_4"
                >Fall 4: Hauseinführung/Leerrohr vorhanden</label
              >
            </td>
          </tr>
        </table>

        <!-- New table -->
        <table class="bp-table bp-noborder-top" style="">
          <tr>
            <td colspan="5" class="bp-bg-dark">
              <b>Kabellängen insgesamt in Metern:</b>
            </td>
          </tr>
          <tr class="bp-bg-b bp-noborder-top">
            <td class="bp-noborder-top bp-w35">
              <b>Gartenbohrung / Trassenverlauf</b>
            </td>
            <td
              class="bp-noborder-top bp-10"
              style="text-align: center"
              id="bp_meter_main_trasse"
            >
              <b>0</b>
            </td>
            <td class="bp-noborder-top bp-20" style="background-color: #fff">
              <b></b>
            </td>
            <td class="bp-noborder-top bp-w35"><b>Kabellänge HÜP / TA</b></td>
            <td
              class="bp-noborder-top bp-10"
              style="text-align: center"
              id="bp_meter_sub_hupta"
            >
              <b>0</b>
            </td>
          </tr>
          <tr class="bp-bg-b bp-noborder-top">
            <td class="bp-noborder-top bp-w35"><b>Kabellänge zum HÜP</b></td>
            <td
              class="bp-noborder-top bp-10"
              style="text-align: center"
              id="bp_meter_main_tohup"
            >
              <b>0</b>
            </td>
            <td class="bp-noborder-top bp-20" style="background-color: #fff">
              <b></b>
            </td>
            <td class="bp-noborder-top bp-w35"><b>Reserve</b></td>
            <td
              class="bp-noborder-top bp-10"
              style="text-align: center"
              id="bp_meter_sub_reserve"
            >
              <b>0</b>
            </td>
          </tr>
          <tr class="bp-bg-b bp-noborder-top">
            <td class="bp-noborder-top bp-w35"><b>Gesamtlänge</b></td>
            <td
              class="bp-noborder-top bp-10"
              style="text-align: center"
              id="bp_meter_main_total"
            >
              <b>0</b>
            </td>
            <td class="bp-noborder-top bp-20" style="background-color: #fff">
              <b></b>
            </td>
            <td class="bp-noborder-top bp-w35"><b>Gesamtlänge</b></td>
            <td
              class="bp-noborder-top bp-10"
              style="text-align: center"
              id="bp_meter_sub_total"
            >
              <b>0</b>
            </td>
          </tr>
        </table>

        <div>
          <div class="row align-items-center mb-2">
            <div class="col-12">
              <div class="bp-w100 bp-bg-dark">
                <b
                  >Sonstige Anmerkungen und/oder relevante Informationen zur
                  Hausbegehung:</b
                >
              </div>
              <textarea
                class="form-control"
                id="bp_commenttohbg"
                rows="5"
                placeholder=""
                style="width: 99%"
              ></textarea>
            </div>
          </div>
        </div>
      </div>
      <!-- page 1 end-->
      <div id="bp_page_2" style="padding: 5mm">
        <div class="" style="border: 1px solid; margin-bottom: 20px">
          <div class="bp-w100 bp-bg-b" style="text-align: center">
            <b>Hauseinführung Aussen</b>
          </div>
          <div class="bp-w100">
            <!-- First Row -->
            <div class="rowrow" style="height: 300px" id="bp_img_HE_aussen">
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
            </div>
          </div>
        </div>

        <div class="" style="border: 1px solid; margin-bottom: 20px">
          <div class="bp-w100 bp-bg-b" style="text-align: center">
            <b>Hauseinführung Innen</b>
          </div>
          <!-- Second Row -->
          <div class="rowrow" style="height: 300px" id="bp_img_HE_innen">
            <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
            <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
            <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
          </div>
        </div>

        <div class="" style="border: 1px solid">
          <div class="bp-w100 bp-bg-b" style="text-align: center">
            <b
              >Trassenverlauf vom Gehweg zur Hauswand (Empfehlung, soweit
              technisch realisierbar)</b
            >
          </div>
          <div class="bp-w100">
            <!-- First Row -->
            <div class="rowrow" style="height: 300px" id="bp_img_trasse">
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
            </div>
          </div>
        </div>
      </div>
      <!-- page 2 end-->

      <div id="bp_page_3" style="padding: 5mm">
        <!-- Page 3 Start -->
        <div class="" style="border: 1px solid; margin-bottom: 20px">
          <div class="bp-w100 bp-bg-b" style="text-align: center">
            <b
              >Der Installationsplatz des HÜPs wurde im Haus festgelegt und
              markiert im Raum (inkl. Foto)</b
            >
          </div>
          <div class="bp-w100">
            <!-- First Row -->
            <div class="rowrow" style="height: 300px" id="bp_img_hupnt">
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
            </div>
          </div>
        </div>

        <div class="" style="border: 1px solid; margin-bottom: 20px">
          <div class="bp-w100 bp-bg-b" style="text-align: center">
            <b>Private Leitungen / Hindernisse im Trassenverlauf</b>
          </div>
          <div class="bp-w100">
            <!-- First Row -->
            <div class="rowrow" style="height: 300px" id="bp_img_private">
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
            </div>
          </div>
        </div>
        <div class="" style="border: 1px solid; margin-bottom: 20px">
          <div class="bp-w100 bp-bg-b" style="text-align: center">
            <b>Haus / Briefkasten bzw. Hausnummer</b>
          </div>
          <div class="bp-w100">
            <!-- First Row -->
            <div class="rowrow" style="height: 300px" id="bp_img_haus">
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
            </div>
          </div>
        </div>

        <!-- page 3 end-->
      </div>
      <div id="bp_page_4" style="padding: 5mm">
        <div class="" style="border: 1px solid; margin-bottom: 20px">
          <div class="bp-w100 bp-bg-b" style="text-align: center">
            <b>Luftbild / Katasterbild</b>
          </div>
          <div class="bp-w100">
            <!-- First Row -->
            <div class="rowrow" style="height: 300px" id="bp_img_kataster">
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
              <div class="bp-33-f" style="padding: 5px; min-width: 33%"></div>
            </div>
          </div>
        </div>

        <div class="" style="margin-bottom: 20px">
          <div class="bp-bg-dark" style="border: 1px solid; padding: 8px">
            <b
              >Der Eigentümer wurde über das Ankommen des Tiefbauteams innerhalb
              von 3 bis 5 Arbeitstagen nach Durchführung der
              Hausbegehung-Arbeiten benachrichtigt. Wird diese Frist nicht
              eingehalten, wird der Kunde von dem Team, das die Anschlüsse
              ausführt, kontaktiert und rechtzeitig in Kenntnis gesetzt.
            </b>
          </div>
        </div>

        <div class="rowrow" style="justify-content: center">
          <div class="bp-w30" style="text-align: center; border: 1px solid">
            Ort,Datum
          </div>
          <div class="bp-w35" style="text-align: center; border: 1px solid">
            Unterschrift Techniker
          </div>
          <div class="bp-w35" style="text-align: center; border: 1px solid">
            Unterschrift Bevollmächtigter
          </div>
        </div>
        <div
          class="rowrow"
          style="justify-content: center; margin-bottom: 20px"
        >
          <div
            class="bp-w30"
            style="
              display: flex;
              justify-content: center;
              align-items: center;
              text-align: center;
              height: 135px;
              flex-direction: column;
              border: 1px solid;
            "
            id="bp_dateplace"
          ></div>
          <div
            class="bp-w35"
            style="text-align: center; height: 135px; border: 1px solid"
            id="bp_sign_technician"
          ></div>
          <div
            class="bp-w35"
            style="text-align: center; height: 135px; border: 1px solid"
            id="bp_sign_customer"
          ></div>
        </div>
        <div class="" style="margin-bottom: 20px">
          <div class="bp-bg-dark" style="border: 1px solid; padding: 8px">
            Datenschutzhinweis
          </div>
          <div
            class="bp-bg-b"
            style="border: 1px solid; border-top: none; padding: 8px"
          >
            Zur Erfüllung des Vertrags (auch gem. der Anlage) ist die Meridiam
            Glasfaser berechtigt, die erhobenen personen- und
            gebäudenetzbezogenen Daten innerhalb von Datenverarbeitungsanlagen
            zu speichern und zu verarbeiten. Zu den Daten zählen insbesondere
            Name, Adresse und Kontaktinformationen der Grundstücks- und
            Gebäudeeigentümerin sowie sonstige auftragserhebliche Angaben zum
            Grundstück und zur Auftragsausführung. Rechtsgrundlage für die
            Datenverarbeitung ist Art. 6 Abs. 1b Datenschutz-Grundverordnung.
            Die Löschung der Daten erfolgt, sobald die Daten nicht mehr für die
            Vertragsdurchführung benötigt werden bzw. gemäß der gesetzlichen
            Aufbewahrungsfristen. Zur Vertragserfüllung setzt die Meridiam
            Glasfaser von ihr beauftragte geeignete Dienstleister ein; deren
            Einsatz erfolgt gemäß Artikel 28 Datenschutz-Grundverordnung. Die
            Datenverarbeitung für die gesamte Leistungserbringung erfolgt
            ausschließlich in der europäischen Union. Eine Nutzung der Daten für
            einen anderen als den vorgenannten Vertragserfüllungszweck oder eine
            Übermittlung an sonstige Dritte findet seitens der Meridiam
            Glasfaser nur statt, sofern dies gesetzlich zulässig ist oder die
            Auftraggeberin/Grundstückseigentümerin ausdrücklich eingewilligt
            hat.
          </div>
        </div>
        <!-- page 4 end-->
      </div>
      <!-- body end-->
    </div>
  </div>

  <style>
    .bp-body {
      width: 210mm;
      min-height: 297mm;
      margin: 0 auto;
      /* Center the page on the screen */
      border: 1mm solid black;
      box-sizing: border-box;
      /* Add some padding for readability */
    }

    h4 {
      margin: 0;
      font-size: 18px;
    }

    .bp-table {
      width: 100%;
      border-collapse: collapse;
    }

    .bp-table,
    .bp-table th,
    .bp-table td {
      border: 1px solid rgb(121, 121, 121);
      font-size: 14px;
    }

    .bp-noborder-top {
      border-top: none !important;
    }

    .bp-bg-b {
      background-color: #cccccc;
    }

    .bp-bg-dark {
      background-color: #898989;
    }

    .rowrow {
      display: flex;
      justify-content: space-around;
    }

    .bp-w33-f {
      width: 33%;
      min-width: 33%;
      max-width: 33%;
    }

    .bp-w33 {
      width: 33%;
    }

    .bp-w5 {
      width: 5%;
    }

    .bp-w10 {
      width: 10%;
    }

    .bp-w15 {
      width: 15%;
    }

    .bp-w20 {
      width: 20%;
    }

    .bp-w25 {
      width: 25%;
    }

    .bp-w30 {
      width: 30%;
    }

    .bp-w35 {
      width: 35%;
    }

    .bp-w40 {
      width: 40%;
    }

    .bp-w45 {
      width: 45%;
    }

    .bp-w50 {
      width: 50%;
    }

    .bp-w55 {
      width: 55%;
    }

    .bp-w60 {
      width: 60%;
    }

    .bp-w65 {
      width: 65%;
    }

    .bp-w70 {
      width: 70%;
    }

    .bp-w75 {
      width: 75%;
    }

    .bp-w80 {
      width: 80%;
    }

    .bp-w85 {
      width: 85%;
    }

    .bp-w90 {
      width: 90%;
    }

    .bp-w95 {
      width: 95%;
    }

    .bp-w100 {
      width: 100%;
    }
  </style>
</html>
