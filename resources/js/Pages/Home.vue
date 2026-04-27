<script setup>
    import { ref } from 'vue';

    const props = defineProps({
        uploads: Array,
    });

    const CHUNK_SIZE = 1 * 1024 * 1024 // 1 MB

    const file = ref(null)
    const chunks = ref([])
    const uploadingProgress = ref({})
    const localUploads = ref([...props.uploads])
    const uploading = ref(false)

    function buildChunks(f) {
        const result = []
        let offset = 0
        while (offset < f.size) {
            result.push(f.slice(offset, offset + CHUNK_SIZE))
            offset += CHUNK_SIZE
        }
        return result
    }

    async function submit() {
        uploading.value = true
        chunks.value = buildChunks(file.value)

        let uploadId = null

        for (let i = 0; i < chunks.value.length; i++) {
            const isFirst = i === 0

            const formData = new FormData()
            formData.append('file', chunks.value[i], file.value.name)

            if (!isFirst) {
                formData.append('append_file', uploadId)
            }

            if (i === chunks.value.length - 1) {
                formData.append('last_append', '1')
            }

            const response = await axios.post('/upload', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            })

            const newUpload = response.data.data
            uploadId = newUpload.id

            if (isFirst) {
                localUploads.value.unshift(newUpload)
            }

            uploadingProgress.value[uploadId] = Math.floor((i + 1) / chunks.value.length * 100)
        }

        file.value = null
        uploading.value = false
    }

    window.Echo.channel('dashboard')
        .listen('UploadUpdated', function (data) {

            const updatedUpload = data.upload

            // clear upload progress when server starts processing
            delete uploadingProgress.value[updatedUpload.id]

            // update record in local uploads list
            const objIndex = localUploads.value.findIndex(upload => upload.id === updatedUpload.id)
            if (objIndex !== -1) {
                localUploads.value[objIndex] = updatedUpload
            }
    })
</script>

<template>
<v-app>
    <v-main>
        <v-app-bar title="YoPrint File Import"></v-app-bar>
        <v-container>
            <form @submit.prevent="submit">
                <v-file-input
                    density="compact"
                    label="File input"
                    variant="solo-filled"
                    @input="file = $event.target.files[0]"
                    :model-value="file ? [file] : []"
                    accept=".csv"
                    show-size
                    :disabled="uploading"
                >
                <template v-slot:append>
                    <v-btn @click="submit" :disabled="!file || uploading" :loading="uploading">
                        Upload
                    </v-btn>
                </template>
                </v-file-input>
            </form>
            <v-table>
                <thead>
                    <tr>
                        <th class="text-left">
                            Time
                        </th>
                        <th class="text-left">
                            File Name
                        </th>
                        <th>
                            Status
                        </th>
                        <th>
                            Progress
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="upload in localUploads" :key="upload.id">
                        <td>{{ upload.created_at }}</td>
                        <td>{{ upload.filename }}</td>
                        <td>{{ upload.status }}</td>
                        <td v-if="uploadingProgress[upload.id] !== undefined">
                            {{ uploadingProgress[upload.id] }}%
                        </td>
                        <td v-else-if="upload.processed > 0">
                            {{ Math.floor(upload.processed / upload.total * 100) }}%
                        </td>
                        <td v-else>0%</td>
                    </tr>
                </tbody>
            </v-table>
        </v-container>
    </v-main>
</v-app>
</template>