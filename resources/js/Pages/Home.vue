<script setup>
    import { defineProps, ref } from 'vue';
    import { useForm, router } from '@inertiajs/vue3'

    const props = defineProps({
        uploads: Array,
    });

    const file = ref(null)

    async function submit() {

        let formData = new FormData()
        formData.append('file', file.value)

        const response = await axios.post('/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        const newUpload = response.data.data

        // append new record to html table
        router.replace({
            props: (currentProps) => {
                const uploads = currentProps.uploads

                uploads.unshift(newUpload)

                return {
                    ...currentProps,
                    uploads: uploads
                }
            }
        })
    }

    window.Echo.channel('dashboard')
        .listen('UploadUpdated', function (data) {

            const updatedUpload = data.upload

            // update record in html table
            router.replace({
                props: (currentProps) => {
                    const uploads = currentProps.uploads
                    const objIndex = uploads.findIndex(upload => upload.id === updatedUpload.id)

                    uploads[objIndex] = updatedUpload

                    return {
                        ...currentProps,
                        uploads: uploads
                    }
                }
            })
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
                    accept=".csv"
                    show-size
                >
                <template v-slot:append>
                    <v-btn @click="submit" :disabled="!file">
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
                    <tr v-for="upload in uploads" :key="upload.id">
                        <td>{{ upload.created_at }}</td>
                        <td>{{ upload.filename }}</td>
                        <td>{{ upload.status }}</td>
                        <td v-if="upload.processed > 0">
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