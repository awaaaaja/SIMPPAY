<script setup>
import vueFilePond from 'vue-filepond';
import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';

const FilePond = vueFilePond(
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize,
    FilePondPluginImagePreview
);

const props = defineProps({
    modelValue: { type: [File, null], default: null },
    labelIdle: { type: String, default: 'Seret file ke sini atau <span class="filepond--label-action">Browse</span>' },
    acceptedFileTypes: { type: Array, default: () => ['image/jpeg', 'image/png'] },
    maxFileSize: { type: String, default: '2MB' },
});

const emit = defineEmits(['update:modelValue']);

function handleFilePondInit() {
    // FilePond is ready
}

function handleFileAdd(error, file) {
    if (!error) {
        emit('update:modelValue', file.file);
    }
}

function handleFileRemove() {
    emit('update:modelValue', null);
}
</script>

<template>
    <FilePond
        :name="'filepond-' + Math.random().toString(36).slice(2)"
        :label-idle="labelIdle"
        :accepted-file-types="acceptedFileTypes"
        :max-file-size="maxFileSize"
        :allow-multiple="false"
        :credits="false"
        @init="handleFilePondInit"
        @addfile="handleFileAdd"
        @removefile="handleFileRemove"
    />
</template>
