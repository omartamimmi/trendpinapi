import NavBar from '../Layouts/Web/NavBar';
import Footer from '../Layouts/Web/Footer';
import Main from '../Layouts/Web/Main';

export default function Home() {
    return (
        <div className="flex flex-col min-h-screen">
            <NavBar />
            <Main />
            <Footer />
        </div>
    );
}
