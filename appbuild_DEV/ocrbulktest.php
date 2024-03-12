<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>OCR Test</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@2"></script>
</head>

<body>
    <h1>OCR Test</h1>
    <input type="file" id="fileDropArea" accept="application/pdf" multiple>
    <div id="results"></div>

    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        document.getElementById('fileDropArea').addEventListener('change', function(event) {
            var files = event.target.files;
            var carrier = 'UGG'; // Fixed carrier

            if (files.length === 0) {
                document.getElementById('results').innerText = 'Please select PDF files.';
                return;
            }

            document.getElementById('results').innerText = 'Processing...';
            processFiles(Array.from(files), carrier);
        });

        function processFiles(files, carrier) {
            files.reduce((promise, file) => {
                return promise.then(() => {
                    // New regex to match both old and new formats
                    const match = file.name.match(/(?:\d{4}_\d{2}_\d{2}_\d{2}_\d{2}_)([A-Z0-9]+)|(?:\d+)([A-Za-z]+)_(\d{4}_\d{2}_\d{2}_\d{4})__(\d+)/);
                    let searchString = '';

                    if (match) {
                        if (match[1]) {
                            // Old format
                            searchString = match[1];
                        } else if (match[4]) {
                            // New format
                            searchString = match[4];
                        }

                        if (searchString) {
                            const startTime = Date.now();

                            return performOCR(file, searchString, carrier).then(response => {
                                const endTime = Date.now();
                                const timeTaken = (endTime - startTime) / 1000; // Convert to seconds
                                var resultText = response.found ? "Home-ID found: " + searchString + " at location: " + JSON.stringify(response.bbox) : "Home-ID not found";
                                document.getElementById('results').innerText += `\nFile: ${file.name} - ${resultText} - Time taken: ${timeTaken} seconds`;
                            });
                        }
                    } else {
                        document.getElementById('results').innerText += `\nFile: ${file.name} - Unable to extract Home-ID`;
                    }
                });
            }, Promise.resolve());
        }


        function performOCR(file, searchString, carrier = 'UGG') {
            return new Promise((resolve, reject) => {
                // If the carrier is not 'UGG' or 'GlasfaserPlus', return success without OCR
                if (carrier !== 'UGG' && carrier !== 'GlasfaserPlus') {
                    resolve({
                        found: true
                    });
                    return;
                }

                const reader = new FileReader();

                reader.onload = function(event) {
                    const pdfData = new Uint8Array(event.target.result);
                    const loadingTask = pdfjsLib.getDocument({
                        data: pdfData
                    });

                    loadingTask.promise.then(async function(pdf) {
                        const page = await pdf.getPage(1);
                        const scale = 2; // Adjust this scale as needed
                        const viewport = page.getViewport({
                            scale: scale
                        });
                        const canvas = document.createElement('canvas');
                        const context = canvas.getContext('2d');
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        await page.render({
                            canvasContext: context,
                            viewport: viewport
                        }).promise;

                        // Cropping from 40% to 60% of the page
                        const cropY = viewport.height * 0.0; // Start from 40% of the height
                        const cropHeight = viewport.height * 0.99; // Cover next 20% of the height
                        const cropWidth = viewport.width; // Full width
                        const cropX = 0; // Start from the left edge

                        // Create a new canvas to hold the cropped image
                        const croppedCanvas = document.createElement('canvas');
                        const croppedCtx = croppedCanvas.getContext('2d');
                        croppedCanvas.width = cropWidth;
                        croppedCanvas.height = cropHeight;
                        croppedCtx.drawImage(canvas, cropX, cropY, cropWidth, cropHeight, 0, 0, cropWidth, cropHeight);

                        const imageDataUrl = croppedCanvas.toDataURL('image/png');

                        Tesseract.recognize(imageDataUrl, 'eng', {
                            oem: 2 // Using a faster OCR Engine Mode
                        }).then((result) => {
                            for (const word of result.data.words) {
                                if (word.text.includes(searchString)) {
                                    resolve({
                                        found: true,
                                        bbox: word.bbox // Capture the bounding box immediately
                                    });
                                    return; // Exit the loop as soon as the word is found
                                }
                            }
                            resolve({
                                found: false
                            }); // Resolve as not found if loop completes without finding the word
                        }).catch(error => {
                            reject('Error during OCR: ' + error);
                        });
                    }).catch(error => {
                        reject('Error loading PDF: ' + error);
                    });
                };

                reader.onerror = function(error) {
                    reject('Error reading file: ' + error);
                };

                reader.readAsArrayBuffer(file);
            });
        }
    </script>
</body>

</html>