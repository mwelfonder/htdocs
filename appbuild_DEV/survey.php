<!-- Tabs Navigation -->
<ul class="nav nav-tabs shadow-sm justify-content-evenly" id="surveyTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="tab_pane_first-tab" data-bs-toggle="tab" href="#tab_pane_first" role="tab" aria-controls="tab_pane_first" aria-selected="true">Kunde</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="daten-tab" data-bs-toggle="tab" href="#daten" role="tab" aria-controls="daten" aria-selected="false">Daten</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="fotos-tab" data-bs-toggle="tab" href="#fotos" role="tab" aria-controls="fotos" aria-selected="false">Fotos</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="unterschrift-tab" data-bs-toggle="tab" href="#unterschrift" role="tab" aria-controls="unterschrift" aria-selected="false">Unterschrift</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ubersicht-tab" data-bs-toggle="tab" href="#ubersicht" role="tab" aria-controls="ubersicht" aria-selected="false">Übersicht</a>
    </li>
    <li class="nav-item" id="exitTab" role="presentation" style="background: #d34646;">
        <button id="exitSurvey" class="nav-link text-white">Verlassen</button>
    </li>
</ul>
<div class="container">
    <!-- Tabs Content -->
    <div class="tab-content" id="surveyTabsContent">
        <!-- Kunde Tab -->
        <div class="tab-pane fade show active" id="tab_pane_first" role="tabpanel" aria-labelledby="tab_pane_first">
            <div class="p-4">
                <h4>Kunden Daten</h4>
                <div class="row align-items-center mb-2">
                    <div class="col-3">
                        <label class="form-check" for="kunde_herrfrau">
                            <input class="form-check-input" type="radio" name="kunde_herrfrau" id="kunde_herr" value="kunde_herr">
                            Herr
                        </label>
                    </div>
                    <div class="col-3">
                        <label class="form-check" for="kunde_herrfrau">
                            <input class="form-check-input" type="radio" name="kunde_herrfrau" id="kunde_frau" value="kunde_frau">
                            Frau
                        </label>
                    </div>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-3 text-right"> <!-- Adjusted width and aligned text to the right -->
                        <label for="firstname" class="pr-2">Vorname:</label> <!-- Added padding to the right for spacing -->
                    </div>
                    <div class="col-9">
                        <p id="firstname" class="placeholder-text mb-0"></p>
                    </div>
                </div>

                <div class="row align-items-center mb-2">
                    <div class="col-3 text-right">
                        <label for="lastname" class="pr-2">Nachname:</label>
                    </div>
                    <div class="col-9">
                        <p id="lastname" class="placeholder-text mb-0"></p>
                    </div>
                </div>

                <div class="row align-items-center mb-2">
                    <div class="col-3 text-right">
                        <label for="city" class="pr-2">Ort:</label>
                    </div>
                    <div class="col-9">
                        <p id="city" class="placeholder-text mb-0"></p>
                    </div>
                </div>

                <div class="row align-items-center mb-2">
                    <div class="col-3 text-right">
                        <label for="street" class="pr-2">Straße:</label>
                    </div>
                    <div class="col-9">
                        <p id="street" class="placeholder-text mb-0"></p>
                    </div>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-3 text-right">
                        <label for="email" class="pr-2">Email:</label>
                    </div>
                    <div class="col-9">
                        <p id="email" class="placeholder-text mb-0"></p>
                    </div>
                </div>

                <div class="row align-items-center mb-2">
                    <div class="col-3 text-right">
                        <label for="phone1" class="pr-2">Phone 1:</label>
                    </div>
                    <div class="col-9">
                        <p id="phone1" class="placeholder-text mb-0"></p>
                    </div>
                </div>

                <div class="row align-items-center mb-2">
                    <div class="col-3 text-right">
                        <label for="phone2" class="pr-2">Phone 2:</label>
                    </div>
                    <div class="col-9">
                        <p id="phone2" class="placeholder-text mb-0"></p>
                    </div>
                </div>

                <div class="row align-items-center mb-2">
                    <div class="col-3 text-right">
                        <label for="customerMail" class="pr-2">Mail:</label>
                    </div>
                    <div class="col-9">
                        <input type="tel" id="customerMail" placeholder="" class="form-control">
                    </div>
                </div>

                <!-- Contract Data Section -->
                <h4>Vertragsdaten</h4>

                <div class="row align-items-center mb-2">
                    <div class="col-3 text-right">
                        <label for="homeid" class="pr-2">Home ID:</label>
                    </div>
                    <div class="col-9">
                        <p id="homeid" class="placeholder-text mb-0"></p>
                    </div>
                </div>

                <div class="row align-items-center mb-2">
                    <div class="col-3 text-right">
                        <label for="adressid" class="pr-2">Address ID:</label>
                    </div>
                    <div class="col-9">
                        <p id="adressid" class="placeholder-text mb-0"></p>
                    </div>
                </div>

                <div class="row align-items-center mb-2">
                    <div class="col-3 text-right">
                        <label for="dpnumber" class="pr-2">DP Number:</label>
                    </div>
                    <div class="col-9">
                        <p id="dpnumber" class="placeholder-text mb-0"></p>
                    </div>
                </div>

                <div class="row align-items-center mb-2">
                    <div class="col-3 text-right">
                        <label for="unit" class="pr-2">Unit:</label>
                    </div>
                    <div class="col-9">
                        <p id="unit" class="placeholder-text mb-0"></p>
                    </div>
                </div>

                <div class="row align-items-center mb-2">
                    <div class="col-3 text-right">
                        <label for="isporder" class="pr-2">ISP Order:</label>
                    </div>
                    <div class="col-9">
                        <p id="isporder" class="placeholder-text mb-0"></p>
                    </div>
                </div>
                <!-- HBG durchführung Section -->
                <h4>Begehung wird durchgeführt mit</h4>

                <div class="row align-items-center mb-2">
                    <div class="col-3">
                        <label class="form-check" for="begehungsperson_verwalter">
                            <input class="form-check-input" type="radio" name="begehungperson" id="begehungsperson_verwalter" value="begehungsperson_verwalter">
                            Verwalter
                        </label>
                    </div>
                    <div class="col-3">
                        <label class="form-check" for="begehungsperson_eigentumer">
                            <input class="form-check-input" type="radio" name="begehungperson" id="begehungsperson_eigentumer" value="begehungsperson_eigentumer">
                            Eigentümer
                        </label>
                    </div>
                    <div class="col-3">
                        <label class="form-check" for="begehungsperson_bevollm">
                            <input class="form-check-input" type="radio" name="begehungperson" id="begehungsperson_bevollm" value="begehungsperson_bevollm">
                            Bevollmächtigter (z. B. Mieter)
                        </label>
                    </div>
                </div>

                <div class="row align-items-center mb-2">
                    <div class="col-9">
                        <input type="name" id="begehungsperson_name" placeholder="Name des Verantwortlichem" class="form-control">
                    </div>
                </div>
                <!-- Eigentümer Section -->
                <h4>Gebäude Eigentümer</h4>
                <div class="row align-items-center mb-2">
                    <div class="col-4 text-right">
                        <label for="ownerSwitch" class="pr-2">Kunde ist Eigentümer:</label>
                    </div>
                    <div class="col-8">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="ownerSwitch" checked>
                            <label class="form-check-label" for="ownerSwitch">
                                <i style="color:green;;" class="bi bi-check-lg"></i>
                                <i style="color:red;display:none;" class="bi bi-x-lg"></i>
                            </label>
                        </div>
                    </div>
                </div>
                <!-- Hidden Area Section -->
                <div class="row" id="isOwnerWrapper" style="display: none;">

                    <div class="row align-items-center mb-2">
                        <div class="col-4 text-right">
                            <label for="ownerInput" class="pr-2">Eigentümer Anrede:</label>
                        </div>
                        <div class="col-8">
                            <div class="row align-items-center mb-2">
                                <div class="col-3">
                                    <label class="form-check" for="kunde_herrfrau">
                                        <input class="form-check-input" type="radio" name="owner_herrfrau" id="owner_herr" value="owner_herr">
                                        Herr
                                    </label>
                                </div>
                                <div class="col-3">
                                    <label class="form-check" for="kunde_herrfrau">
                                        <input class="form-check-input" type="radio" name="owner_herrfrau" id="owner_frau" value="owner_frau">
                                        Frau
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row align-items-center mb-2">
                        <div class="col-4 text-right">
                            <label for="newOwnerFirstname" class="pr-2">Eigentümer Vorname:</label>
                        </div>
                        <div class="col-8">
                            <input type="text" id="newOwnerFirstname" placeholder="" class="form-control">
                        </div>
                    </div>
                    <div class="row align-items-center mb-2">
                        <div class="col-4 text-right">
                            <label for="newOwnerLastname" class="pr-2">Eigentümer Nachname:</label>
                        </div>
                        <div class="col-8">
                            <input type="text" id="newOwnerLastname" placeholder="" class="form-control">
                        </div>
                    </div>
                    <div class="row align-items-center mb-2">
                        <div class="col-4 text-right">
                            <label for="newOwnerAdress" class="pr-2">Eigentümer Adresse:</label>
                        </div>
                        <div class="col-8">
                            <input type="text" id="newOwnerAdress" placeholder="" class="form-control">
                        </div>
                    </div>
                    <div class="row align-items-center mb-2">
                        <div class="col-4 text-right">
                            <label for="newOwnerPhone" class="pr-2">Eigentümer Tel.:</label>
                        </div>
                        <div class="col-8">
                            <input type="text" id="newOwnerPhone" placeholder="" class="form-control">
                        </div>
                    </div>
                    <div class="row align-items-center mb-2">
                        <div class="col-4 text-right">
                            <label for="newOwnerMail" class="pr-2">Eigentümer Mail:</label>
                        </div>
                        <div class="col-8">
                            <input type="email" id="newOwnerMail" placeholder="" class="form-control">
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Daten Tab -->
        <div class="tab-pane fade" id="daten" role="tabpanel" aria-labelledby="daten-tab">
            <div class="p-4">
                <h4>Gebäude Daten</h4>
                <div class="row align-items-center mb-2">
                    <div class="col-6 text-right">
                        <label for="housetyp" class="pr-2">Gebäudetyp</label>
                    </div>
                    <div class="col-6">
                        <form>
                            <div class="form-group">
                                <select class="form-control" id="housetyp">
                                    <option value="" disabled selected></option>
                                    <option value="housetyp_EFH">Einfamilienhaus</option>
                                    <option value="housetyp_MFH">Mehrfamilienhaus</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-6 text-right">
                        <label for="wohnungenDropdown">Wohnungen im Gebäude:</label>
                    </div>
                    <div class="col-6">
                        <select class="form-select" id="wohnungenDropdown" name="wohnungen">
                            <option value="" disabled selected></option>
                            <?php
                            for ($i = 1; $i <= 12; $i++) {
                                echo "<option value=\"$i\">$i</option>";
                            }
                            echo '<option value="above_12">Mehr als 12</option>';
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-6 text-right">
                        <label for="kabellangeStrasse">Kabellänge Straße <i class="bi bi-arrow-right-short"></i> Gebäude:</label>
                    </div>
                    <div class="col-6">
                        <input type="number" step="0.01" class="form-control" id="kabellangeStrasse">
                    </div>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-6 text-right">
                        <label for="kabellangeHE">Kabellänge HE <i class="bi bi-arrow-right-short"></i> HÜP:</label>
                    </div>
                    <div class="col-6">
                        <input type="number" step="0.01" class="form-control" id="kabellangeHE">
                    </div>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-6 text-right">
                        <label for="kabellangeHUP">Kabellänge HÜP <i class="bi bi-arrow-right-short"></i> TA:</label>
                    </div>
                    <div class="col-6">
                        <input type="number" step="0.01" class="form-control" id="kabellangeHUP">
                    </div>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-6 text-right">
                        <label for="kabellangeTA">Kabellänge TA <i class="bi bi-arrow-right-short"></i> NT:</label>
                    </div>
                    <div class="col-6">
                        <input type="number" step="0.01" class="form-control" id="kabellangeTA">
                    </div>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-6 text-right">
                        <label for="gesamtmeter">Gesamtmeter:</label>
                    </div>
                    <div class="col-6">
                        <input type="number" step="0.01" class="form-control" id="gesamtmeter" value="0.00" readonly>
                    </div>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-6 text-right">
                        <label for="trasse_privateleitungen" class="pr-2">Private Leitungen im Trassenverlauf?</label>
                    </div>
                    <div class="col-6">
                        <div class="form-check form-switch form-switch-thick">
                            <input class="form-check-input" type="checkbox" id="trasse_privateleitungen">
                            <label class="form-check-label" for="trasse_privateleitungen">
                                <i style="color:green;display:none;" class="bi bi-check-lg"></i>
                                <i style="color:red;" class="bi bi-x-lg"></i>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-6 text-right">
                        <label for="trasse_leerrohrevorhanden" class="pr-2">Leerrohre für den Trassenverlauf vorhanden?</label>
                    </div>
                    <div class="col-6">
                        <div class="form-check form-switch form-switch-thick">
                            <input class="form-check-input" type="checkbox" id="trasse_leerrohrevorhanden">
                            <label class="form-check-label" for="trasse_leerrohrevorhanden">
                                <i style="color:green;display:none;" class="bi bi-check-lg"></i>
                                <i style="color:red;" class="bi bi-x-lg"></i>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center mb-2" id="row_leerrohreHinweisWrapper" style="display: none;">
                    <div class="alert alert-warning" role="alert">
                        <b>Hinweis:</b> Bei Nutzung von durch den Kunden verlegten und geeigneten Leerrohren, sind beide Rohrenden rechzeitig bis zum Installationstermin vom Kunden freizulegen (beide Kopflöcher).
                    </div>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-6 text-right">
                        <label for="trasse_HEvorhanden" class="pr-2">Hauseinführung vorhanden?</label>
                    </div>
                    <div class="col-6">
                        <div class="form-check form-switch form-switch-thick">
                            <input class="form-check-input" type="checkbox" id="trasse_HEvorhanden">
                            <label class="form-check-label" for="trasse_HEvorhanden">
                                <i style="color:green;display:none;" class="bi bi-check-lg"></i>
                                <i style="color:red;" class="bi bi-x-lg"></i>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center mb-2" style="display: none;" id="row_HEartWrapper">
                    <div class="col-6 text-right">
                        <label for="trasse_HEart" class="pr-2">Art der HE</label>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="trasse_HEart" id="einzeleinfuehrung">
                            <label class="form-check-label" for="einzeleinfuehrung">
                                Einzeleinführung
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="trasse_HEart" id="mehrspartenanschluss">
                            <label class="form-check-label" for="mehrspartenanschluss">
                                Mehrspartenanschluss
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center mb-2" id="row_HEpunktWrapper">
                    <div class="col-6 text-right">
                        <label for="trasse_HEvorhanden" class="pr-2">Hauseinführungspunkt:</label>
                    </div>
                    <div class="col-6">
                        <form>
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="HEselection" id="HE_UK" value="option1">
                                    <label class="form-check-label" for="HE_UK">Unterirdisch, Kellergeschoss</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="HEselection" id="HE_UE" value="option2">
                                    <label class="form-check-label" for="HE_UE">Unterirdisch, Erdgeschoss</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="HEselection" id="HE_OE" value="option3">
                                    <label class="form-check-label" for="HE_OE">Oberirdisch, Erdgeschoss</label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-6 text-right">
                        <label for="trasse_leerrohrevorhandenHE" class="pr-2">Leerrohre für die Hauseinführung vorhanden und nutzbar?</label>
                    </div>
                    <div class="col-6">
                        <div class="form-check form-switch form-switch-thick">
                            <input class="form-check-input" type="checkbox" id="trasse_leerrohrevorhandenHE">
                            <label class="form-check-label" for="trasse_leerrohrevorhandenHE">
                                <i style="color:green;display:none;" class="bi bi-check-lg"></i>
                                <i style="color:red;" class="bi bi-x-lg"></i>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center mb-2" id="row_leerrohreHinweisWrapper" style="display: none;">
                    <div class="alert alert-warning" role="alert">
                        <b>Hinweis:</b> Bei Nutzung von durch den Kunden verlegten und geeigneten Leerrohren bis in das Gebäude, sind beide Rohrenden rechzeitig bis zum Installationstermin vom Kunden freizulegen (beide Kopflöcher).
                    </div>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-6 text-right">
                        <label for="standortHup" class="pr-2">Standort HÜP</label>
                    </div>
                    <div class="col-6">
                        <form>
                            <div class="form-group">
                                <select class="form-control" id="huproomselection">
                                    <option value="" disabled selected></option>
                                    <option value="keller">Keller</option>
                                    <option value="garage">Garage</option>
                                    <option value="hwrtechnikraum">HWR/Technikraum</option>
                                    <option value="flur">Flur</option>
                                    <option value="wohnzimmer">Wohnzimmer</option>
                                    <option value="buero">Büro</option>
                                    <option value="sonstiges">Sonstiges</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <hr>

                <div class="row align-items-center mb-2">
                    <div class="col-12">
                        <label for="commentField" class="form-label"><b>Sonstige Anmerkungen und/oder relevante Informationen</b></label>
                        <textarea class="form-control" id="commentField_hbgnoteimportant" rows="3" placeholder="Bemerkung zur Hausbegehung"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <!-- Fotos Tab -->
        <div class="tab-pane fade" id="fotos" role="tabpanel" aria-labelledby="fotos-tab">
            <div class="p-4">
                <h4>Dokumentation</h4>
                <!-- Outside of the building photo upload -->
                <div class="photo-section mt-4">
                    <!-- <h5>Trassenverlauf <span class="" data-bs-toggle="tooltip" data-bs-html="true" title="<strong>Custom HTML Tooltip</strong><br/>More details here."><i class="bi bi-info-circle-fill"></i></span></h5> -->
                    <h5>
                        Trassenverlauf
                        <span data-bs-toggle="modal" data-bs-target="#tooltipModal"><i class="bi bi-info-circle-fill"></i></span>
                    </h5>
                    <!-- Photo Previews -->
                    <div class="row" data-context="trassenverlauf">
                        <!-- Preview 1 -->
                        <div class="col-md-3">
                            <div class="photo-container">
                                <img id="trassenverlauf_preview1" src="images/noimageplaceholder.jpg" alt="Vorschau 1" class="img-fluid object-fit-contain">
                            </div>
                        </div>
                        <!-- Preview 2 -->
                        <div class="col-md-3">
                            <div class="photo-container">
                                <img id="trassenverlauf_preview2" src="images/noimageplaceholder.jpg" alt="Vorschau 2" class="img-fluid object-fit-contain">
                            </div>
                        </div>
                        <!-- Preview 3 -->
                        <div class="col-md-3">
                            <div class="photo-container">
                                <img id="trassenverlauf_preview3" src="images/noimageplaceholder.jpg" alt="Vorschau 3" class="img-fluid object-fit-contain">
                            </div>
                        </div>
                    </div>

                    <!-- Photo Buttons -->
                    <div class="photo-options mt-3">
                        <label class="takePic btn btn-primary mr-2">
                            <i class="bi bi-camera"></i> Kamera
                            <input type="file" accept="image/*;capture=camera" capture="camera" multiple style="display: none;" data-context="trassenverlauf">
                        </label>

                        <label class="selectPic btn btn-secondary">
                            <i class="bi bi-card-image"></i> Galerie
                            <input type="file" accept="image/*" multiple style="display: none;" data-context="trassenverlauf">
                        </label>
                    </div>
                </div>

                <!-- HE aussen -->
                <div class="photo-section mt-4">
                    <h5>Hauseinführung außen</h5>
                    <!-- Photo Previews -->
                    <div class="row" data-context="heaussen">
                        <!-- Preview 1 -->
                        <div class="col-md-3">
                            <div class="photo-container">
                                <img id="heaussen_preview1" src="images/noimageplaceholder.jpg" alt="Vorschau 1" class="img-fluid">
                            </div>
                        </div>
                        <!-- Preview 2 -->
                        <div class="col-md-3">
                            <div class="photo-container">
                                <img id="heaussen_preview2" src="images/noimageplaceholder.jpg" alt="Vorschau 2" class="img-fluid">
                            </div>
                        </div>
                    </div>

                    <!-- Photo Buttons -->
                    <div class="photo-options mt-3">
                        <label class="takePic btn btn-primary mr-2">
                            <i class="bi bi-camera"></i> Kamera
                            <input type="file" accept="image/*;capture=camera" capture="camera" multiple style="display: none;" data-context="heaussen">
                        </label>

                        <label class="selectPic btn btn-secondary">
                            <i class="bi bi-card-image"></i> Galerie
                            <input type="file" accept="image/*" multiple style="display: none;" data-context="heaussen">
                        </label>
                    </div>
                </div>
                <!-- HE innen -->
                <div class="photo-section mt-4">
                    <h5>Hauseinführung innen</h5>
                    <!-- Photo Previews -->
                    <div class="row" data-context="heinnen">
                        <!-- Preview 1 -->
                        <div class="col-md-3">
                            <div class="photo-container">
                                <img id="heinnen_preview1" src="images/noimageplaceholder.jpg" alt="Vorschau 1" class="img-fluid">
                            </div>
                        </div>
                        <!-- Preview 2 -->
                        <div class="col-md-3">
                            <div class="photo-container">
                                <img id="heinnen_preview2" src="images/noimageplaceholder.jpg" alt="Vorschau 2" class="img-fluid">
                            </div>
                        </div>
                    </div>

                    <!-- Photo Buttons -->
                    <div class="photo-options mt-3">
                        <label class="takePic btn btn-primary mr-2">
                            <i class="bi bi-camera"></i> Kamera
                            <input type="file" accept="image/*;capture=camera" capture="camera" multiple style="display: none;" data-context="heinnen">
                        </label>

                        <label class="selectPic btn btn-secondary">
                            <i class="bi bi-card-image"></i> Galerie
                            <input type="file" accept="image/*" multiple style="display: none;" data-context="heinnen">
                        </label>
                    </div>
                </div>
                <!-- HÜP NT -->
                <div class="photo-section mt-4">
                    <!-- <h5>Trassenverlauf <span class="" data-bs-toggle="tooltip" data-bs-html="true" title="<strong>Custom HTML Tooltip</strong><br/>More details here."><i class="bi bi-info-circle-fill"></i></span></h5> -->
                    <h5>
                        Platzierung HÜP / NT
                        <span data-bs-toggle="modal" data-bs-target="#tooltipModal"><i class="bi bi-info-circle-fill"></i></span>
                    </h5>
                    <!-- Photo Previews -->
                    <div class="row" data-context="placehupnt">
                        <!-- Preview 1 -->
                        <div class="col-md-3">
                            <div class="photo-container">
                                <img id="placehupnt_preview1" src="images/noimageplaceholder.jpg" alt="Vorschau 1" class="img-fluid object-fit-contain">
                            </div>
                        </div>
                        <!-- Preview 2 -->
                        <div class="col-md-3">
                            <div class="photo-container">
                                <img id="placehupnt_preview2" src="images/noimageplaceholder.jpg" alt="Vorschau 2" class="img-fluid object-fit-contain">
                            </div>
                        </div>
                        <!-- Preview 3 -->
                        <div class="col-md-3">
                            <div class="photo-container">
                                <img id="placehupnt_preview3" src="images/noimageplaceholder.jpg" alt="Vorschau 3" class="img-fluid object-fit-contain">
                            </div>
                        </div>
                    </div>

                    <!-- Photo Buttons -->
                    <div class="photo-options mt-3">
                        <label class="takePic btn btn-primary mr-2">
                            <i class="bi bi-camera"></i> Kamera
                            <input type="file" accept="image/*;capture=camera" capture="camera" multiple style="display: none;" data-context="placehupnt">
                        </label>

                        <label class="selectPic btn btn-secondary">
                            <i class="bi bi-card-image"></i> Galerie
                            <input type="file" accept="image/*" multiple style="display: none;" data-context="placehupnt">
                        </label>
                    </div>
                </div>
                <!-- Briefkasten -->
                <div class="photo-section mt-4">
                    <h5>Briefkasten / Hausnummer</h5>
                    <!-- Photo Previews -->
                    <div class="row" data-context="hausnummer">
                        <!-- Preview 1 -->
                        <div class="col-md-3">
                            <div class="photo-container">
                                <img id="hausnummer_preview1" src="images/noimageplaceholder.jpg" alt="Vorschau 1" class="img-fluid">
                            </div>
                        </div>
                        <!-- Preview 2 -->
                        <div class="col-md-3">
                            <div class="photo-container">
                                <img id="hausnummer_preview2" src="images/noimageplaceholder.jpg" alt="Vorschau 2" class="img-fluid">
                            </div>
                        </div>
                    </div>

                    <!-- Photo Buttons -->
                    <div class="photo-options mt-3">
                        <label class="takePic btn btn-primary mr-2">
                            <i class="bi bi-camera"></i> Kamera
                            <input type="file" accept="image/*;capture=camera" capture="camera" multiple style="display: none;" data-context="hausnummer">
                        </label>

                        <label class="selectPic btn btn-secondary">
                            <i class="bi bi-card-image"></i> Galerie
                            <input type="file" accept="image/*" multiple style="display: none;" data-context="hausnummer">
                        </label>
                    </div>
                </div>
                <!-- PrivateLeitungen -->
                <div class="photo-section mt-4">
                    <h5>Private Leitungen</h5>
                    <!-- Photo Previews -->
                    <div class="row" data-context="privateleitungen">
                        <!-- Preview 1 -->
                        <div class="col-md-3">
                            <div class="photo-container">
                                <img id="privateleitungen_preview1" src="images/noimageplaceholder.jpg" alt="Vorschau 1" class="img-fluid">
                            </div>
                        </div>
                        <!-- Preview 2 -->
                        <div class="col-md-3">
                            <div class="photo-container">
                                <img id="privateleitungen_preview2" src="images/noimageplaceholder.jpg" alt="Vorschau 2" class="img-fluid">
                            </div>
                        </div>
                    </div>

                    <!-- Photo Buttons -->
                    <div class="photo-options mt-3">
                        <label class="takePic btn btn-primary mr-2">
                            <i class="bi bi-camera"></i> Kamera
                            <input type="file" accept="image/*;capture=camera" capture="camera" multiple style="display: none;" data-context="privateleitungen">
                        </label>

                        <label class="selectPic btn btn-secondary">
                            <i class="bi bi-card-image"></i> Galerie
                            <input type="file" accept="image/*" multiple style="display: none;" data-context="privateleitungen">
                        </label>
                    </div>
                </div>
                <!-- Kataster -->
                <div class="photo-section mt-4">
                    <h5>Luftbild / Kataster</h5>
                    <!-- Photo Previews -->
                    <div class="row" data-context="kataster">
                        <!-- Preview 1 -->
                        <div class="col-md-3">
                            <div class="photo-container">
                                <img id="kataster_preview1" src="images/noimageplaceholder.jpg" alt="Vorschau 1" class="img-fluid">
                            </div>
                        </div>
                        <!-- Preview 2 -->
                        <div class="col-md-3">
                            <div class="photo-container">
                                <img id="kataster_preview2" src="images/noimageplaceholder.jpg" alt="Vorschau 2" class="img-fluid">
                            </div>
                        </div>
                        <!-- Preview 3 -->
                        <div class="col-md-3">
                            <div class="photo-container">
                                <img id="kataster_preview3" src="images/noimageplaceholder.jpg" alt="Vorschau 3" class="img-fluid">
                            </div>
                        </div>
                    </div>

                    <!-- Photo Buttons -->
                    <div class="photo-options mt-3">
                        <label class="takePic btn btn-primary mr-2">
                            <i class="bi bi-camera"></i> Kamera
                            <input type="file" accept="image/*;capture=camera" capture="camera" multiple style="display: none;" data-context="kataster">
                        </label>

                        <label class="selectPic btn btn-secondary">
                            <i class="bi bi-card-image"></i> Galerie
                            <input type="file" accept="image/*" multiple style="display: none;" data-context="kataster">
                        </label>
                    </div>
                </div>


            </div>
        </div>

        <!-- Unterschrift Tab -->
        <div class="tab-pane fade" id="unterschrift" role="tabpanel" aria-labelledby="unterschrift-tab">
            <div class="p-4">
                <div class="container">
                    <div class="row">
                        <!-- Technician Signature Preview -->
                        <div class="col-md-6 mb-3">
                            <div class="card" id="sign_preview_technican">
                                <div class="card-body">
                                    <div class="p-5 signPreview" id="technican_signature_preview"></div>
                                </div>
                                <div class="card-footer d-flex justify-content-between">
                                    <span>Unterschrift Techniker</span><button type="button" class="btn btn-sm btn-primary"><i class="bi bi-pencil-square"></i></button>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Signature Preview -->
                        <div class="col-md-6 mb-3">
                            <div class="card" id="sign_preview_customer">
                                <div class="card-body">
                                    <div class="p-5 signPreview" id="customer_signature_preview"></div>
                                </div>
                                <div class="card-footer d-flex justify-content-between">
                                    <span>Unterschrift Kunde</span><button type="button" class="btn btn-sm btn-primary"><i class="bi bi-pencil-square"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="alert alert-secondary" role="alert">
                        <h3 class="alert-heading">Datenschutzhinweis</h3>
                        <hr>
                        <p class="mb-0">Zur Erfüllung des Vertrags (auch gem. der Anlage) ist die Meridiam Glasfaser berechtigt, die erhobenen personen- und gebäudenetzbezogenen Daten innerhalb von Datenverarbeitungsanlagen zu speichern und zu verarbeiten. Zu den Daten zählen insbesondere Name, Adresse und Kontaktinformationen der Grundstücks- und Gebäudeeigentümerin sowie sonstige auftragserhebliche Angaben zum Grundstück und zur Auftragsausführung. Rechtsgrundlage für die Datenverarbeitung ist Art. 6 Abs. 1b Datenschutz-Grundverordnung. Die Löschung der Daten erfolgt, sobald die Daten nicht mehr für die Vertragsdurchführung benötigt werden bzw. gemäß der gesetzlichen Aufbewahrungsfristen. Zur Vertragserfüllung setzt die Meridiam Glasfaser von ihr beauftragte geeignete Dienstleister ein; deren Einsatz erfolgt gemäß Artikel 28 Datenschutz-Grundverordnung. Die Datenverarbeitung für die gesamte Leistungserbringung erfolgt ausschließlich in der europäischen Union. Eine Nutzung der Daten für einen anderen als den vorgenannten Vertragserfüllungszweck oder eine Übermittlung an sonstige Dritte findet seitens der Meridiam Glasfaser nur statt, sofern dies gesetzlich zulässig ist oder die Auftraggeberin/Grundstückseigentümerin ausdrücklich eingewilligt hat.</p>
                    </div>
                </div>

            </div>
        </div>

        <!-- Übersicht Tab -->
        <div class="tab-pane fade" id="ubersicht" role="tabpanel" aria-labelledby="ubersicht-tab">
            <!-- Content for Übersicht -->
            <div class="p-4">
                <button id="surveyPrint" type="button" class="btn btn-primary">HBG Erledigt</button>
            </div>
        </div>

    </div>
</div>

<!-- The modal structure -->
<div class="modal fade" id="tooltipModal" tabindex="-1" aria-labelledby="tooltipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tooltipModalLabel">Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <strong>Custom HTML Tooltip</strong><br />More details here.
            </div>
        </div>
    </div>
</div>

<!-- The Lightbox structure -->
<div class="lightbox" id="customLightbox" style="display: none;">
    <div class="top-bar">
        <button id="lb_remove" class="btn btn-danger btn-sm ml-auto"><i class="bi bi-trash3"></i> Löschen</button>
        <button id="lb_returnorig" class="btn btn-light btn-sm ml-auto">Original Bild</button>
        <button id="lb_undo" class="btn btn-light btn-sm ml-auto"><i class="bi bi-arrow-bar-left"></i></button>
        <button id="lightboxClose" class="btn btn-primary btn-sm ml-auto">Schließen</button>
    </div>

    <div class="sidebar">
        <span class="addCanvas hup">HÜP</span>
        <span class="addCanvas nt">NT</span>
        <span class="addCanvas he">HE</span>
        <span class="addCanvas arrow"><i class="bi bi-arrow-up"></i></span>
        <span class="addCanvas font"><i class="bi bi-fonts"></i></span>
    </div>

    <canvas id="lightboxCanvas"></canvas>
</div>




<style>

</style>