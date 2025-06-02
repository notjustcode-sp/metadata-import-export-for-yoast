document.addEventListener('DOMContentLoaded', function() {
    // Export functionality
    const exportButton = document.getElementById('export-button');
    if (exportButton) {
        exportButton.addEventListener('click', async function () {
            const progressBar = document.getElementById('export-progress-bar');
            const progressFill = document.getElementById('export-progress-fill');
            progressBar.style.display = 'block';
            progressFill.style.width = '0%';

            try {
                const formData = new FormData();
                formData.append('action', 'export_yoast_csv');
                formData.append('nonce', YoastMetadataAjax.exportNonce);

                const response = await fetch(YoastMetadataAjax.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                });

                const data = await response.json();

                if (data.success) {
                    progressFill.style.width = '100%';
                    const fileUrl = data.data.file_url;
                    const downloadLink = document.createElement('a');
                    downloadLink.href = fileUrl;
                    downloadLink.download = data.data.file_name;
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    document.body.removeChild(downloadLink);

                    // Display the export data
                    const exportDataDiv = document.getElementById('export-data');
                    if (exportDataDiv) {
                        // Clear previous content
                        exportDataDiv.innerHTML = '';

                        const totalPosts = data.data.total_posts;
                        const postsWithMetadata = data.data.posts_with_metadata;
                        const postsWithoutMetadata = data.data.posts_without_metadata;
                        const postTypeCounts = data.data.post_type_counts;

                        let postTypeCountsHtml = '';
                        for (const [postType, count] of Object.entries(postTypeCounts)) {
                            postTypeCountsHtml += `<tr><td>${postType}</td><td>${count}</td></tr>`;
                        }

                        const summaryHtml = `
                            <div class="miey-summary">
                                <h3>Export Summary</h3>
                                <table class="miey-summary-table">
                                    <tr>
                                        <th>Total Posts</th>
                                        <td>${totalPosts}</td>
                                    </tr>
                                    <tr>
                                        <th>Posts with Metadata</th>
                                        <td>${postsWithMetadata}</td>
                                    </tr>
                                    <tr>
                                        <th>Posts without Metadata</th>
                                        <td>${postsWithoutMetadata}</td>
                                    </tr>
                                </table>
                                <h4>Post Counts per Post Type</h4>
                                <table class="miey-post-type-table">
                                    <tr>
                                        <th>Post Type</th>
                                        <th>Count</th>
                                    </tr>
                                    ${postTypeCountsHtml}
                                </table>
                            </div>
                        `;

                        exportDataDiv.innerHTML = summaryHtml;
                    }
                } else {
                    console.error('Error:', data.data ? data.data.message : 'Unknown error');
                }
            } catch (error) {
                console.error('Fetch Error:', error);
            }
        });
    }

    // Import functionality
    const importForm = document.getElementById('yoast-import-form');
    if (importForm) {
        importForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const importProgressBar = document.getElementById('import-progress-bar');
            const importProgressFill = document.getElementById('import-progress-fill');
            importProgressBar.style.display = 'block';
            importProgressFill.style.width = '0%';

            const csvFileInput = document.getElementById('csv_file');
            if (csvFileInput.files.length === 0) {
                alert('Please select a CSV file to upload.');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'import_yoast_csv');
            formData.append('nonce', YoastMetadataAjax.importNonce);
            formData.append('csv_file', csvFileInput.files[0]);

            try {
                const response = await fetch(YoastMetadataAjax.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                });

                const data = await response.json();

                if (data.success) {
                    importProgressFill.style.width = '100%';

                    // Display the import data
                    const importDataDiv = document.getElementById('import-data');
                    if (importDataDiv) {
                        // Clear previous content
                        importDataDiv.innerHTML = '';

                        const importStats = data.data.import_stats;
                        const errors = data.data.errors;

                        let postTypeStatsHtml = '';
                        for (const [postType, stats] of Object.entries(importStats.post_type_counts)) {
                            postTypeStatsHtml += `
                                <tr>
                                    <td>${postType}</td>
                                    <td>${stats.posts_updated}</td>
                                    <td>${stats.field_updates.slug}</td>
                                    <td>${stats.field_updates.keyphrase}</td>
                                    <td>${stats.field_updates.seo_title}</td>
                                    <td>${stats.field_updates.seo_description}</td>
                                </tr>
                            `;
                        }

                        let errorsHtml = '';
                        if (errors.length > 0) {
                            errorsHtml = '<h4>Errors:</h4><ul>';
                            errors.forEach(function(error) {
                                errorsHtml += `<li>${error}</li>`;
                            });
                            errorsHtml += '</ul>';
                        }

                        const summaryHtml = `
                            <div class="miey-summary">
                                <h3>Import Summary</h3>
                                <table class="miey-summary-table">
                                    <tr>
                                        <th>Total Rows Processed</th>
                                        <td>${importStats.total_rows}</td>
                                    </tr>
                                    <tr>
                                        <th>Posts Updated</th>
                                        <td>${importStats.posts_updated}</td>
                                    </tr>
                                    <tr>
                                        <th>Slugs Updated</th>
                                        <td>${importStats.field_updates.slug}</td>
                                    </tr>
                                    <tr>
                                        <th>Keyphrases Updated</th>
                                        <td>${importStats.field_updates.keyphrase}</td>
                                    </tr>
                                    <tr>
                                        <th>SEO Titles Updated</th>
                                        <td>${importStats.field_updates.seo_title}</td>
                                    </tr>
                                    <tr>
                                        <th>SEO Descriptions Updated</th>
                                        <td>${importStats.field_updates.seo_description}</td>
                                    </tr>
                                </table>
                                <h4>Updates per Post Type</h4>
                                <table class="miey-post-type-table">
                                    <tr>
                                        <th>Post Type</th>
                                        <th>Posts Updated</th>
                                        <th>Slugs</th>
                                        <th>Keyphrases</th>
                                        <th>SEO Titles</th>
                                        <th>SEO Descriptions</th>
                                    </tr>
                                    ${postTypeStatsHtml}
                                </table>
                                ${errorsHtml}
                            </div>
                        `;

                        importDataDiv.innerHTML = summaryHtml;
                    }
                } else {
                    console.error('Error:', data.data ? data.data.message : 'Unknown error');
                    alert('Error: ' + (data.data ? data.data.message : 'Unknown error'));
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                alert('Fetch Error: ' + error.message);
            }
        });
    }
});
