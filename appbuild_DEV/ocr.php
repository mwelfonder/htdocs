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
                    const match = file.name.match(/(?:\d{4}_\d{2}_\d{2}_\d{2}_\d{2}_)([A-Z0-9]+)/);
                    if (match && match[1]) {
                        const searchString = match[1];
                        const startTime = Date.now();

                        return performOCR(file, searchString, carrier).then(response => {
                            const endTime = Date.now();
                            const timeTaken = (endTime - startTime) / 1000; // Convert to seconds
                            var resultText = response.found ? "Home-ID found: " + searchString + " at location: " + JSON.stringify(response.bbox) : "Home-ID not found";
                            document.getElementById('results').innerText += `\nFile: ${file.name} - ${resultText} - Time taken: ${timeTaken} seconds`;
                        });
                    } else {
                        document.getElementById('results').innerText += `\nFile: ${file.name} - Unable to extract Home-ID`;
                    }
                });
            }, Promise.resolve());
        }

        function performOCR(file, searchString, carrier = 'UGG') {

            const searchAreas = [
                { bbox: { x0: 1110, y0: 550, x1: 1300, y1: 630 }, matches: 6 },
                { bbox: { x0: 1117, y0: 600, x1: 1300, y1: 630 }, matches: 2 },
                // Add other areas as needed
            ];

            return new Promise(async (resolve, reject) => {
                if (carrier !== 'UGG' && carrier !== 'GlasfaserPlus') {
                    resolve({
                        found: true
                    });
                    return;
                }

                const reader = new FileReader();

                reader.onload = async function(event) {
                    const pdfData = new Uint8Array(event.target.result);
                    const loadingTask = pdfjsLib.getDocument({
                        data: pdfData
                    });

                    try {
                        const pdf = await loadingTask.promise;
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

                        // Sort search areas by match frequency
                        searchAreas.sort((a, b) => b.matches - a.matches);

                        for (const area of searchAreas) {
                            const {
                                x0,
                                y0,
                                x1,
                                y1
                            } = area.bbox;
                            const cropWidth = x1 - x0;
                            const cropHeight = y1 - y0;
                            const croppedCanvas = document.createElement('canvas');
                            const croppedCtx = croppedCanvas.getContext('2d');
                            croppedCanvas.width = cropWidth;
                            croppedCanvas.height = cropHeight;
                            croppedCtx.drawImage(canvas, x0, y0, cropWidth, cropHeight, 0, 0, cropWidth, cropHeight);

                            const imageDataUrl = croppedCanvas.toDataURL('image/png');
                            const result = await Tesseract.recognize(imageDataUrl, 'eng', {
                                oem: 2
                            });

                            for (const word of result.data.words) {
                                if (word.text.includes(searchString)) {
                                    resolve({
                                        found: true,
                                        bbox: word.bbox
                                    });
                                    return;
                                }
                            }
                        }
                        resolve({
                            found: false
                        });
                    } catch (error) {
                        reject('Error during OCR: ' + error);
                    }
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