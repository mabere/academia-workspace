window.courseDetailDashboard = function (config = {}) {
    return {
        tab: config.initialTab || 'materials',
        docModalOpen: false,
        docModalTitle: '',
        docModalUrl: '',
        docDownloadUrl: '',
        editMaterialModalOpen: false,
        editMaterialData: {
            id: null,
            title: '',
            session_number: '',
            material_type: 'document',
            external_url: '',
            content_text: '',
            description: '',
            file_name: '',
            file_size: '',
            update_url: ''
        },
        openEditMaterialModal(data) {
            this.editMaterialData = { ...data };
            this.editMaterialModalOpen = true;
        },
        materialSearch: '',
        materialFilter: 'all',
        materialSessionFilter: 'all',
        assignmentSearch: '',
        assignmentSessionFilter: 'all',
        assignmentStatusFilter: 'all',
        studentSearch: '',
        assignOpen: false,
        assignmentType: 'writing',
        submissionModalOpen: false,
        activeSub: null,
        activeRevIndex: 0,
        activeCopySuccess: false,
        openSubmissionModal(subData) {
            this.activeSub = subData;
            if (subData && subData.revisions && subData.revisions.length > 0) {
                this.activeRevIndex = subData.revisions.length - 1;
            } else {
                this.activeRevIndex = 0;
            }
            this.submissionModalOpen = true;
            this.activeCopySuccess = false;
        },
        currentRevision() {
            if (!this.activeSub || !this.activeSub.revisions || this.activeSub.revisions.length === 0) {
                return null;
            }
            return this.activeSub.revisions[this.activeRevIndex] || this.activeSub.revisions[0];
        },
        currentWordCount() {
            const rev = this.currentRevision();
            if (!rev || !rev.body_text) return 0;
            const trimmed = rev.body_text.trim();
            return trimmed ? trimmed.split(/\s+/).length : 0;
        },
        copyCurrentText() {
            const rev = this.currentRevision();
            if (!rev || !rev.body_text) return;
            navigator.clipboard.writeText(rev.body_text).then(() => {
                this.activeCopySuccess = true;
                setTimeout(() => { this.activeCopySuccess = false; }, 2500);
            });
        },
        aiModalOpen: false,
        aiGenerating: false,
        aiError: null,
        aiResult: null,
        sessionsData: config.sessionsData || [],
        aiSessionId: '',
        aiTopic: '',
        aiFocus: '',
        aiType: 'writing',
        aiBloom: 'Analisis (C4)',
        aiIncludeRubric: true,
        aiNotes: '',
        aiProvider: config.aiProvider || 'deepseek',
        aiAppliedToast: false,
        selectedSessionInfo() {
            if (!this.aiSessionId) return null;
            return this.sessionsData.find(s => s.id == this.aiSessionId) || null;
        },
        openDocPreview(title, viewerUrl, dlUrl) {
            this.docModalTitle = title;
            this.docModalUrl = viewerUrl;
            this.docDownloadUrl = dlUrl;
            this.docModalOpen = true;
        },
        openAiModal() {
            this.aiModalOpen = true;
            this.aiError = null;
            if (!this.aiSessionId && this.sessionsData.length > 0) {
                const latest = this.sessionsData[this.sessionsData.length - 1];
                this.aiSessionId = latest.id;
            }
        },
        async generateAssignmentWithAi() {
            const currentSession = this.selectedSessionInfo();
            if (!this.aiSessionId && (!this.aiTopic || !this.aiTopic.trim())) {
                this.aiError = 'Silakan pilih sesi pertemuan perkuliahan atau masukkan topik penugasan terlebih dahulu.';
                return;
            }
            this.aiGenerating = true;
            this.aiError = null;
            this.aiResult = null;

            try {
                const response = await fetch(config.aiGenerateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        session_id: this.aiSessionId || null,
                        topic: currentSession ? currentSession.topic : this.aiTopic,
                        assignment_focus: this.aiFocus || null,
                        assignment_type: this.aiType,
                        bloom_level: this.aiBloom,
                        include_rubric: this.aiIncludeRubric,
                        notes: this.aiNotes,
                        provider: this.aiProvider
                    })
                });

                const json = await response.json();
                if (!response.ok || !json.success) {
                    throw new Error(json.message || 'Gagal menghasilkan tugas dengan AI.');
                }

                this.aiResult = json.data;
            } catch (err) {
                this.aiError = err.message || 'Terjadi gangguan saat memproses AI.';
            } finally {
                this.aiGenerating = false;
            }
        },
        applyAiToForm() {
            if (!this.aiResult) return;

            const titleInput = document.getElementById('assignment_title_input');
            const instructionsTextarea = document.getElementById('assignment_instructions_input');
            const sessionSelect = document.getElementById('assignment_course_session_id');

            if (titleInput) {
                titleInput.value = this.aiResult.title;
            }

            let instructions = (this.aiResult.instructions || '').trim();
            instructions = instructions.replace(/(?<!^)(?<!\n)\s+(\d+\.\s+)/g, '\n\n$1').trim();
            instructions = instructions.replace(/(?<!\n)\s+([a-z]\.\s+)/g, '\n   $1').trim();

            let fullText = "PETUNJUK & LANGKAH PENGERJAAN TUGAS:\n\n" + instructions;

            if (this.aiResult.rubric && this.aiResult.rubric.length > 0) {
                fullText += '\n\n' + '='.repeat(55) + '\n';
                fullText += 'RUBRIK PENILAIAN TUGAS (CRITERIA & WEIGHTS)\n';
                fullText += '='.repeat(55) + '\n';
                this.aiResult.rubric.forEach((r, idx) => {
                    fullText += `\n${idx + 1}. ${r.criterion} (Bobot: ${r.weight_percentage}%)\n   Standar Pencapaian: ${r.description}\n`;
                });
            }

            if (instructionsTextarea) {
                instructionsTextarea.value = fullText;
            }

            if (this.aiResult.assignment_type) {
                this.assignmentType = this.aiResult.assignment_type;
            }

            if (this.aiSessionId && sessionSelect) {
                sessionSelect.value = this.aiSessionId;
            }

            this.assignOpen = true;
            this.aiModalOpen = false;
            this.aiAppliedToast = true;
            setTimeout(() => { this.aiAppliedToast = false; }, 5000);

            this.$nextTick(() => {
                if (titleInput) {
                    titleInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    titleInput.focus();
                }
            });
        }
    };
}
