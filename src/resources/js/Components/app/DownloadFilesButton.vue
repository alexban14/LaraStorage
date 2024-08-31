<template>
    <PrimaryButton @click="download">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15M9 12l3 3m0 0 3-3m-3 3V2.25" />
        </svg>
        Download
    </PrimaryButton>
</template>

<script setup>
import ConfirmationDialog from "@/Components/app/ConfirmationDialog.vue";
import {useForm, usePage} from "@inertiajs/vue3";
import {showErrorDialog} from "@/event-bus.js";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {httpGet} from "@/Helpers/http-helper.js";
import {useRoute} from "vue-router";

const page = usePage();
const route = useRoute();

const props = defineProps({
    all: {
        type: Boolean,
        required: false,
        default: false
    },
    ids: {
        type: Array,
        default: []
    },
    downloadRoute: {
        type: String,
        // default:
    },
    sharedType: {
        type: String,
        default: '',
    }
})

function download() {
    if (!props.all && props.ids.length === 0) {
        return;
    }

    const p = new URLSearchParams();
    if (page.props.folder) {
        p.append('parent_id', page.props.folder.data.id);
    }
    if (props.all) {
        p.append('all', '1');
    } else {
        for (let id of props.ids) {
            p.append('ids[]', id);
        }
    }
    if (props.sharedType) {
        p.append('shared_type', props.sharedType)
    }

    httpGet(props.downloadRoute + '?' + p.toString())
        .then(res => {
            const a = document.createElement('a');
            a.download = res.filename;
            a.href = res.url;
            a.click();
        })
}
</script>

<style scoped>

</style>
