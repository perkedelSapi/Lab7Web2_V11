const { createApp } = Vue

// Sesuaikan dengan path CI4 kamu
const apiUrl = 'http://localhost/lab11_ci/ci4/public'

createApp({
    data() {
        return {
            artikel: [],
            loading: false,
            errorMsg: '',
            showForm: false,
            formTitle: 'Tambah Data',
            formData: {
                id: null,
                judul: '',
                isi: '',
                status: 0
            },
            statusOptions: [
                { text: 'Draft',   value: 0 },
                { text: 'Publish', value: 1 },
            ],
        }
    },

    mounted() {
        this.loadData()
    },

    methods: {

        // Ambil semua data dari API
        loadData() {
            this.loading = true
            axios.get(apiUrl + '/post')
                .then(response => {
                    this.artikel = response.data.artikel
                    this.loading = false
                })
                .catch(error => {
                    console.log(error)
                    this.loading = false
                })
        },

        // Buka form tambah
        tambah() {
            this.showForm  = true
            this.formTitle = 'Tambah Data'
            this.errorMsg  = ''
            this.formData  = { id: null, judul: '', isi: '', status: 0 }
        },

        // Buka form edit
        edit(data) {
            this.showForm  = true
            this.formTitle = 'Edit Data'
            this.errorMsg  = ''
            this.formData  = {
                id:     data.id,
                judul:  data.judul,
                isi:    data.isi,
                status: data.status
            }
        },

        // Simpan data (tambah atau edit)
        saveData() {
            this.errorMsg = ''

            if (!this.formData.judul.trim()) {
                this.errorMsg = 'Judul tidak boleh kosong.'
                return
            }

            if (this.formData.id) {
                // Update
                axios.put(apiUrl + '/post/' + this.formData.id, this.formData)
                    .then(() => {
                        this.showForm = false
                        this.loadData()
                    })
                    .catch(error => {
                        this.errorMsg = 'Gagal mengubah data.'
                        console.log(error)
                    })
            } else {
                // Insert
                axios.post(apiUrl + '/post', this.formData)
                    .then(() => {
                        this.showForm = false
                        this.loadData()
                    })
                    .catch(error => {
                        this.errorMsg = 'Gagal menyimpan data.'
                        console.log(error)
                    })
            }
        },

        // Hapus data
        hapus(index, id) {
            if (confirm('Yakin menghapus data ini?')) {
                axios.delete(apiUrl + '/post/' + id)
                    .then(() => {
                        this.artikel.splice(index, 1)
                    })
                    .catch(error => {
                        alert('Gagal menghapus data.')
                        console.log(error)
                    })
            }
        },

        // Konversi status angka ke teks
        statusText(status) {
            return status == 1 ? 'Publish' : 'Draft'
        }
    }

}).mount('#app')